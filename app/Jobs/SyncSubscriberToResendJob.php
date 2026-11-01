<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Subscriber;
use App\Services\Resend\ResendClientInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * Copies an opted-in subscriber to Resend: the general segment, plus the welcome offer's
 * when that is how they signed up. Runs on the queue and retries, so Resend being down
 * never fails a sign-up.
 */
final class SyncSubscriberToResendJob implements ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public readonly Subscriber $subscriber) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(ResendClientInterface $resend): void
    {
        $general = config('services.resend.segments.general');
        $welcomeOffer = config('services.resend.segments.welcome_offer');

        if (! is_string($general) || $general === '' || ! $this->subscriber->hasMarketingConsent()) {
            return;
        }

        $segmentIds = [$general];

        if ($this->subscriber->hasWelcomeCode() && is_string($welcomeOffer) && $welcomeOffer !== '') {
            $segmentIds[] = $welcomeOffer;
        }

        $this->subscriber->loadMissing('user');

        $contactId = $resend->subscribeContact(
            $this->subscriber->email,
            $this->subscriber->user === null ? null : Str::before($this->subscriber->user->name, ' '),
            $segmentIds,
        );

        $this->subscriber->update([
            'resend_contact_id' => $contactId,
            'synced_to_resend_at' => Date::now(),
        ]);
    }
}
