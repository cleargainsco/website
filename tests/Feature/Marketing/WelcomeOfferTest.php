<?php

declare(strict_types=1);

use App\Http\Controllers\Storefront\WelcomeOfferClaimController;
use App\Jobs\SyncSubscriberToResendJob;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\WelcomeOffer;
use App\Notifications\Marketing\WelcomeOfferNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Shop\Models\DiscountCode;

function enableWelcomeOffer(array $attributes = []): WelcomeOffer
{
    return tap(WelcomeOffer::current())->update(['is_enabled' => true, ...$attributes]);
}

describe('claiming the offer', function () {
    beforeEach(function () {
        Notification::fake();
        Queue::fake([SyncSubscriberToResendJob::class]);
    });

    test('a visitor gets a single-use code for their address by email and joins the list', function () {
        $this->freezeTime();
        enableWelcomeOffer(['percent_off' => 15, 'valid_days' => 30]);

        $this->post(route('welcome-offer.store'), ['email' => 'Ada@Example.com'])
            ->assertRedirect()
            ->assertCookie(WelcomeOfferClaimController::COOKIE, '1', encrypted: false);

        $subscriber = Subscriber::query()->with('discountCode')->sole();

        expect($subscriber->email)->toBe('ada@example.com')
            ->and($subscriber->hasMarketingConsent())->toBeTrue()
            ->and($subscriber->discountCode->code)->toMatch('/^[A-HJ-NP-Z2-9]{6}$/')
            ->and($subscriber->discountCode->percent_off)->toBe(15)
            ->and($subscriber->discountCode->email)->toBe('ada@example.com')
            ->and($subscriber->discountCode->usage_limit)->toBe(1)
            ->and($subscriber->discountCode->expires_at?->toDateString())->toBe(now()->addDays(30)->toDateString());

        Notification::assertSentOnDemand(
            WelcomeOfferNotification::class,
            fn (WelcomeOfferNotification $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'ada@example.com'
                && $notification->discountCode->is($subscriber->discountCode),
        );
        Queue::assertPushed(SyncSubscriberToResendJob::class, fn (SyncSubscriberToResendJob $job): bool => $job->subscriber->is($subscriber));
    });

    test('asking again re-sends the same code instead of issuing another', function () {
        enableWelcomeOffer();

        $this->post(route('welcome-offer.store'), ['email' => 'ada@example.com']);
        $this->post(route('welcome-offer.store'), ['email' => 'ada@example.com']);

        expect(DiscountCode::query()->count())->toBe(1);
        Notification::assertSentOnDemandTimes(WelcomeOfferNotification::class, 2);
    });

    test('an expired code that was never used is replaced', function () {
        enableWelcomeOffer();
        $old = DiscountCode::factory()->expired()->forEmail('ada@example.com')->create();
        Subscriber::factory()->create(['email' => 'ada@example.com', 'discount_code_id' => $old->id]);

        $this->post(route('welcome-offer.store'), ['email' => 'ada@example.com']);

        expect(Subscriber::query()->sole()->discount_code_id)->not->toBe($old->id);
    });

    test('an address that has spent its code gets no other', function () {
        enableWelcomeOffer();
        $spent = DiscountCode::factory()->singleUse()->forEmail('ada@example.com')->create(['times_used' => 1]);
        Subscriber::factory()->create(['email' => 'ada@example.com', 'discount_code_id' => $spent->id]);

        $this->post(route('welcome-offer.store'), ['email' => 'ada@example.com']);

        expect(DiscountCode::query()->count())->toBe(1);
        Notification::assertNothingSent();
    });

    test('nobody can sign up while the offer is off', function () {
        $this->post(route('welcome-offer.store'), ['email' => 'ada@example.com'])
            ->assertSessionHasErrors(['email' => 'This offer has ended.']);

        expect(Subscriber::query()->count())->toBe(0);
    });

    test('the email address must be valid', function () {
        enableWelcomeOffer();

        $this->post(route('welcome-offer.store'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');
    });
});

describe('who sees the popup', function () {
    test('a first-time guest gets the copy while the offer is on', function () {
        enableWelcomeOffer(['headline' => 'Take 15% off']);

        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page->where('welcomeOffer.headline', 'Take 15% off'));
    });

    test('nobody gets it while the offer is off, once they have seen it, or when signed in', function () {
        $this->get(route('home'))->assertInertia(fn (Assert $page) => $page->where('welcomeOffer', null));

        enableWelcomeOffer();

        $this->withUnencryptedCookie(WelcomeOfferClaimController::COOKIE, '1')
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page->where('welcomeOffer', null));

        $this->actingAs(User::factory()->customer()->create())
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page->where('welcomeOffer', null));
    });
});
