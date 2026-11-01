<?php

declare(strict_types=1);

use App\Enums\SubscriberSource;
use App\Jobs\SyncSubscriberToResendJob;
use App\Models\Subscriber;
use App\Models\User;
use App\Services\Resend\ResendClientInterface;
use Illuminate\Support\Facades\Queue;
use Shop\Models\DiscountCode;
use Tests\Support\FakeResendClient;

beforeEach(function () {
    $this->resend = new FakeResendClient;
    $this->app->instance(ResendClientInterface::class, $this->resend);
    config(['services.resend.segments' => ['general' => 'seg_general', 'welcome_offer' => 'seg_welcome']]);
});

describe('the sync job', function () {
    test('adds an opted-in customer to the general segment and records the contact', function () {
        $subscriber = Subscriber::factory()->create([
            'source' => SubscriberSource::Registration,
            'email' => 'ada@example.com',
            'user_id' => User::factory()->create(['name' => 'Ada Lovelace'])->id,
        ]);

        SyncSubscriberToResendJob::dispatchSync($subscriber);

        expect($this->resend->subscribed)->toBe([['email' => 'ada@example.com', 'first_name' => 'Ada', 'segment_ids' => ['seg_general']]])
            ->and($subscriber->refresh()->resend_contact_id)->toBe('contact_1')
            ->and($subscriber->isSyncedToResend())->toBeTrue();
    });

    test('puts a welcome offer sign-up in its own segment as well', function () {
        SyncSubscriberToResendJob::dispatchSync(Subscriber::factory()->create([
            'discount_code_id' => DiscountCode::factory()->create()->id,
        ]));

        expect($this->resend->subscribed[0]['segment_ids'])->toBe(['seg_general', 'seg_welcome']);
    });

    test('sends nobody without marketing consent', function () {
        SyncSubscriberToResendJob::dispatchSync(Subscriber::factory()->withoutConsent()->create());

        expect($this->resend->subscribed)->toBe([]);
    });

    test('sends nobody until the general segment is configured', function () {
        config(['services.resend.segments.general' => null]);

        SyncSubscriberToResendJob::dispatchSync(Subscriber::factory()->create());

        expect($this->resend->subscribed)->toBe([]);
    });
});

describe('the catch-up command', function () {
    test('queues every opted-in subscriber Resend does not have yet', function () {
        Queue::fake([SyncSubscriberToResendJob::class]);
        $waiting = Subscriber::factory()->create();
        Subscriber::factory()->synced()->create();
        Subscriber::factory()->withoutConsent()->create();

        $this->artisan('app:resend:sync-subscribers')->assertSuccessful();

        Queue::assertPushedTimes(SyncSubscriberToResendJob::class, 1);
        Queue::assertPushed(SyncSubscriberToResendJob::class, fn (SyncSubscriberToResendJob $job): bool => $job->subscriber->is($waiting));
    });
});
