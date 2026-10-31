<?php

declare(strict_types=1);

namespace Shop\Exceptions;

use RuntimeException;

final class EmptyCartException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('There is nothing in the cart to order.');
    }
}
