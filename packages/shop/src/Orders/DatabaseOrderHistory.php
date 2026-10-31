<?php

declare(strict_types=1);

namespace Shop\Orders;

use Shop\Contracts\OrderHistory;
use Shop\Models\OrderLine;
use Shop\Models\Product;

final class DatabaseOrderHistory implements OrderHistory
{
    public function hasBeenOrdered(Product $product): bool
    {
        return OrderLine::query()->where('product_id', $product->id)->exists();
    }
}
