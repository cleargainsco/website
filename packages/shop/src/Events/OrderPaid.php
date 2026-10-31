<?php

declare(strict_types=1);

namespace Shop\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shop\Models\Order;

final class OrderPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Order $order) {}
}
