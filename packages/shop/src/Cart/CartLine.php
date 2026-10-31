<?php

declare(strict_types=1);

namespace Shop\Cart;

use Brick\Money\Money;
use Shop\Models\Product;

final readonly class CartLine
{
    public function __construct(
        public Product $product,
        public int $quantity,
        public Money $unitPrice,
    ) {}

    public function total(): Money
    {
        return $this->unitPrice->multipliedBy($this->quantity);
    }
}
