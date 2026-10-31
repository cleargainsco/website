<?php

declare(strict_types=1);

namespace Shop\Contracts;

use Shop\Models\Product;

interface OrderHistory
{
    public function hasBeenOrdered(Product $product): bool;
}
