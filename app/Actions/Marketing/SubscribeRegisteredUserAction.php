<?php

declare(strict_types=1);

namespace App\Actions\Marketing;

use App\Enums\SubscriberSource;
use App\Jobs\SyncSubscriberToResendJob;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Support\Facades\Date;

/**
 * Links a new account to the marketing list. Only someone who ticked the opt-in is
 * added; an address already on the list from the welcome offer is linked either way.
 */
final readonly class SubscribeRegisteredUserAction
{
    public function handle(User $user, bool $marketingOptIn): ?Subscriber
    {
        $email = mb_strtolower($user->email);

        $subscriber = Subscriber::query()->where('email', $email)->first();

        if ($subscriber === null && ! $marketingOptIn) {
            return null;
        }

        $subscriber ??= new Subscriber(['email' => $email, 'source' => SubscriberSource::Registration]);
        $subscriber->user()->associate($user);

        if ($marketingOptIn) {
            $subscriber->marketing_consent_at ??= Date::now();
        }

        $subscriber->save();

        if ($subscriber->hasMarketingConsent() && ! $subscriber->isSyncedToResend()) {
            SyncSubscriberToResendJob::dispatch($subscriber);
        }

        return $subscriber;
    }
}
