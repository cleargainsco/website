<?php

declare(strict_types=1);

namespace App\Actions\Marketing;

use App\Data\WelcomeOfferData;
use App\Models\WelcomeOffer;

/**
 * Codes already sent keep the percentage and expiry they were issued with.
 */
final readonly class UpdateWelcomeOfferAction
{
    public function handle(WelcomeOfferData $data): WelcomeOffer
    {
        $offer = WelcomeOffer::current();

        $offer->fill([
            'percent_off' => $data->percentOff,
            'valid_days' => $data->validDays,
            'eyebrow' => $data->eyebrow,
            'headline' => $data->headline,
            'body' => $data->body,
            'button_label' => $data->buttonLabel,
            'success_headline' => $data->successHeadline,
            'success_body' => $data->successBody,
            'fine_print' => $data->finePrint,
        ])->save();

        return $offer;
    }
}
