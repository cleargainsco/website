<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\WelcomeOffer;
use Illuminate\Support\Facades\Date;
use Shop\Models\DiscountCode;

/**
 * A welcome code that is never saved, built from the current offer, for the email preview.
 */
final class SampleDiscountCode
{
    public static function welcome(): DiscountCode
    {
        $offer = WelcomeOffer::current();

        return new DiscountCode([
            'code' => '7KQ4XM',
            'percent_off' => $offer->percent_off,
            'usage_limit' => 1,
            'expires_at' => $offer->valid_days === null ? null : Date::now()->addDays($offer->valid_days)->endOfDay(),
        ]);
    }
}
