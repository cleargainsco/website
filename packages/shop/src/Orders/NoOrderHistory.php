<?php

declare(strict_types=1);

namespace Shop\Orders;

use Shop\Contracts\OrderHistory;
use Shop\Models\Product;

/**
 * The answer until the shop takes orders: nothing has been ordered.
 */
final class NoOrderHistory implements OrderHistory
{
    public function hasBeenOrdered(Product $product): bool
    {
        return false;
    }
}
