<?php

declare(strict_types=1);

namespace Shop\Contracts;

use Brick\Money\Money;
use Shop\Models\Product;

interface PriceResolver
{
    public function unitPrice(Product $product, int $quantity): Money;
}
