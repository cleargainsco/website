<?php

declare(strict_types=1);

namespace Shop\Exceptions;

use DomainException;
use Shop\Models\DiscountCode;

final class DiscountCodeUnavailableException extends DomainException
{
    public static function notFound(string $code): self
    {
        return new self(__('We couldn’t find the code :code.', ['code' => $code]));
    }

    public static function expired(DiscountCode $discount): self
    {
        return new self(__('The code :code has expired.', ['code' => $discount->code]));
    }

    public static function usedUp(DiscountCode $discount): self
    {
        return new self(__('The code :code has already been used.', ['code' => $discount->code]));
    }

    public static function forAnotherCustomer(DiscountCode $discount): self
    {
        return new self(__('The code :code was sent to a different email address.', ['code' => $discount->code]));
    }
}
