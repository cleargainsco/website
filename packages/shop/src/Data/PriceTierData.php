<?php

declare(strict_types=1);

namespace Shop\Data;

use Brick\Money\Money;

final readonly class PriceTierData
{
    public function __construct(
        public int $minQuantity,
        public Money $unitPrice,
    ) {}
}
