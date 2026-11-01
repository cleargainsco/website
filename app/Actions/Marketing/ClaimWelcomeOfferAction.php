<?php

declare(strict_types=1);

namespace App\Actions\Marketing;

use App\Enums\SubscriberSource;
use App\Exceptions\WelcomeOfferClosedException;
use App\Jobs\SyncSubscriberToResendJob;
use App\Models\Subscriber;
use App\Models\WelcomeOffer;
use App\Notifications\Marketing\WelcomeOfferNotification;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Shop\Models\DiscountCode;

/**
 * Signs the address up for marketing and emails it a single-use code tied to it. Asking
 * again re-sends the code it already has; only an expired, unused one is replaced, and
 * an address that has spent its code gets no other.
 */
final readonly class ClaimWelcomeOfferAction
{
    /**
     * No 0/O or 1/I, which are easy to misread when typed from an email.
     */
    private const string CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const int CODE_LENGTH = 6;

    /**
     * @throws WelcomeOfferClosedException
     */
    public function handle(string $email): Subscriber
    {
        $offer = WelcomeOffer::current();

        throw_unless($offer->isEnabled(), WelcomeOfferClosedException::make());

        $subscriber = DB::transaction(function () use ($offer, $email): Subscriber {
            $subscriber = Subscriber::query()->with('discountCode')->firstOrNew(
                ['email' => $email],
                ['source' => SubscriberSource::WelcomeOffer],
            );

            $subscriber->marketing_consent_at ??= Date::now();

            if ($this->needsNewCode($subscriber->discountCode)) {
                $subscriber->discountCode()->associate(DiscountCode::query()->create([
                    'code' => $this->uniqueCode(),
                    'percent_off' => $offer->percent_off,
                    'email' => $email,
                    'usage_limit' => 1,
                    'expires_at' => $offer->valid_days === null ? null : Date::now()->addDays($offer->valid_days)->endOfDay(),
                ]));
            }

            $subscriber->save();

            return $subscriber;
        });

        if ($subscriber->discountCode !== null && ! $subscriber->discountCode->isUsedUp()) {
            Notification::route('mail', $email)->notify(new WelcomeOfferNotification($subscriber->discountCode));
        }

        if (! $subscriber->isSyncedToResend()) {
            SyncSubscriberToResendJob::dispatch($subscriber);
        }

        return $subscriber;
    }

    private function needsNewCode(?DiscountCode $current): bool
    {
        return $current === null || ($current->isExpired() && ! $current->isUsedUp());
    }

    private function uniqueCode(): string
    {
        do {
            $code = implode('', array_map(
                fn (): string => self::CODE_ALPHABET[random_int(0, mb_strlen(self::CODE_ALPHABET) - 1)],
                range(1, self::CODE_LENGTH),
            ));
        } while (DiscountCode::query()->where('code', $code)->exists());

        return $code;
    }
}
