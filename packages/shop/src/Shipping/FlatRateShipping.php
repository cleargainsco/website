<?php

declare(strict_types=1);

namespace Shop\Shipping;

use Brick\Money\Money;
use Illuminate\Support\Collection;
use Shop\Contracts\ShippingCalculator;

/**
 * One flat charge per order, set in `shop.shipping.flat_rate` in major units. Left
 * empty, shipping is confirmed later, with the invoice.
 */
final class FlatRateShipping implements ShippingCalculator
{
    public function quote(Collection $lines): ?Money
    {
        $rate = config('shop.shipping.flat_rate');

        if ($rate === null || $rate === '') {
            return null;
        }

        return Money::of((string) $rate, config()->string('shop.currency'));
    }
}
