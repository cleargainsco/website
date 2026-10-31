<?php

declare(strict_types=1);

namespace Shop\Actions;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Shop\Enums\OrderStatus;
use Shop\Events\OrderPaid;
use Shop\Exceptions\OrderStateException;
use Shop\Models\Order;

final readonly class MarkOrderPaidAction
{
    /**
     * @throws OrderStateException
     */
    public function handle(Order $order): Order
    {
        $paid = DB::transaction(function () use ($order): Order {
            $locked = Order::query()->lockForUpdate()->whereKey($order->id)->firstOrFail();

            if (! $locked->isAwaitingPayment()) {
                throw OrderStateException::cannot('marked paid', $locked);
            }

            $locked->update(['status' => OrderStatus::Paid, 'paid_at' => Date::now()]);

            return $locked;
        });

        OrderPaid::dispatch($paid);

        return $paid;
    }
}
