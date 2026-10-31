<?php

declare(strict_types=1);

namespace Shop\Contracts;

use Brick\Money\Money;
use Illuminate\Support\Collection;
use Shop\Cart\CartLine;

interface ShippingCalculator
{
    /**
     * The shipping charge for these lines, or null when it is confirmed later.
     *
     * @param  Collection<int, CartLine>  $lines
     */
    public function quote(Collection $lines): ?Money;
}
