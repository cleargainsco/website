<?php

declare(strict_types=1);

namespace Shop\Exceptions;

use RuntimeException;
use Shop\Models\Product;

final class ProductNotPricedException extends RuntimeException
{
    public static function forQuantity(Product $product, int $quantity): self
    {
        return new self("[{$product->slug}] has no price for a quantity of {$quantity}.");
    }
}
