<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

final class WelcomeOfferClosedException extends DomainException
{
    public static function make(): self
    {
        return new self(__('This offer has ended.'));
    }
}
