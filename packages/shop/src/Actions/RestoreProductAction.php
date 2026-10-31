<?php

declare(strict_types=1);

namespace Shop\Actions;

use Shop\Models\Product;

final readonly class RestoreProductAction
{
    public function handle(Product $product): void
    {
        $product->restore();
    }
}
