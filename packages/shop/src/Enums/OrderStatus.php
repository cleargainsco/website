<?php

declare(strict_types=1);

namespace Shop\Enums;

enum OrderStatus: string
{
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';
}
