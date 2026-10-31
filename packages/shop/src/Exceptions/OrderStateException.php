<?php

declare(strict_types=1);

namespace Shop\Exceptions;

use RuntimeException;
use Shop\Models\Order;

final class OrderStateException extends RuntimeException
{
    public static function cannot(string $action, Order $order): self
    {
        return new self("Order {$order->number} is {$order->status->value} and cannot be {$action}.");
    }
}
