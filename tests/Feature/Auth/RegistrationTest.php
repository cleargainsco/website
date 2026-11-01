<?php

use App\Enums\RoleName;
use App\Enums\SubscriberSource;
use App\Jobs\SyncSubscriberToResendJob;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('account.index', absolute: false));

    expect(User::query()->where('email', 'test@example.com')->sole()->getRoleNames()->all())
        ->toBe([RoleName::Customer->value]);
});

describe('the marketing opt-in', function () {
    beforeEach(fn () => Queue::fake([SyncSubscriberToResendJob::class]));

    test('ticking it adds the new customer to the list and sends them to Resend', function () {
        $this->post(route('register.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'marketing_opt_in' => '1',
        ]);

        $subscriber = Subscriber::query()->sole();

        expect($subscriber->source)->toBe(SubscriberSource::Registration)
            ->and($subscriber->user_id)->toBe(User::query()->sole()->id)
            ->and($subscriber->hasMarketingConsent())->toBeTrue();
        Queue::assertPushed(SyncSubscriberToResendJob::class);
    });

    test('leaving it unticked adds nobody', function () {
        $this->post(route('register.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        expect(Subscriber::query()->count())->toBe(0);
        Queue::assertNothingPushed();
    });

    test('an address already on the list from the welcome offer is linked to the account', function () {
        $subscriber = Subscriber::factory()->synced()->create(['email' => 'ada@example.com']);

        $this->post(route('register.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        expect($subscriber->refresh()->user_id)->toBe(User::query()->sole()->id)
            ->and($subscriber->source)->toBe(SubscriberSource::WelcomeOffer);
    });
});
