<?php

declare(strict_types=1);

namespace Shop\Exceptions;

use RuntimeException;
use Shop\Models\Product;

final class InsufficientStockException extends RuntimeException
{
    public function __construct(public readonly Product $product, public readonly int $requested)
    {
        parent::__construct("Only {$product->stock} of [{$product->slug}] left; {$requested} requested.");
    }
}
