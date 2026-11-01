<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SyncSubscriberToResendJob;
use App\Models\Subscriber;
use Illuminate\Console\Command;

/**
 * Queues every opted-in subscriber Resend doesn't have yet: those who signed up before
 * the segment was configured, and any whose sync ran out of retries.
 */
final class SyncSubscribersToResendCommand extends Command
{
    protected $signature = 'app:resend:sync-subscribers';

    protected $description = 'Send opted-in subscribers that are not in Resend yet';

    public function handle(): int
    {
        if (blank(config('services.resend.segments.general'))) {
            $this->components->warn('RESEND_GENERAL_SEGMENT_ID is not set, so there is nowhere to send them.');

            return self::SUCCESS;
        }

        $queued = 0;

        Subscriber::query()
            ->whereNotNull('marketing_consent_at')
            ->whereNull('synced_to_resend_at')
            ->lazyById()
            ->each(function (Subscriber $subscriber) use (&$queued): void {
                SyncSubscriberToResendJob::dispatch($subscriber);
                $queued++;
            });

        $this->components->info("Queued {$queued} subscribers for Resend.");

        return self::SUCCESS;
    }
}
