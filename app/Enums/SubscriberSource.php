<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriberSource: string
{
    case WelcomeOffer = 'welcome_offer';
    case Registration = 'registration';

    public function label(): string
    {
        return match ($this) {
            self::WelcomeOffer => 'Welcome offer',
            self::Registration => 'Registration',
        };
    }
}
