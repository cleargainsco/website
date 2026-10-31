<?php

declare(strict_types=1);

namespace Shop\Actions;

use Shop\Models\Product;

final readonly class IncrementStockAction
{
    public function handle(Product $product, int $quantity): void
    {
        Product::withTrashed()->whereKey($product->id)->increment('stock', $quantity);
    }
}
