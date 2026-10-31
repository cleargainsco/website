<?php

declare(strict_types=1);

namespace Shop\Exceptions;

use RuntimeException;
use Shop\Models\Product;

final class ProductUnavailableException extends RuntimeException
{
    public function __construct(public readonly Product $product)
    {
        parent::__construct("[{$product->slug}] is not for sale right now.");
    }
}
