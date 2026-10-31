<?php

declare(strict_types=1);

namespace Shop\Actions;

use Shop\Contracts\OrderHistory;
use Shop\Enums\ProductRemoval;
use Shop\Models\Product;

/**
 * A product nobody has ordered is deleted for good, image and prices with it. One that
 * has been ordered is archived instead, so the orders that point at it keep their history.
 */
final readonly class RemoveProductAction
{
    public function __construct(private OrderHistory $orders) {}

    public function handle(Product $product): ProductRemoval
    {
        if ($this->orders->hasBeenOrdered($product)) {
            $product->delete();

            return ProductRemoval::Archived;
        }

        $product->forceDelete();

        return ProductRemoval::Deleted;
    }
}
