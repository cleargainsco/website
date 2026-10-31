<?php

declare(strict_types=1);

namespace Shop\Actions;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Shop\Data\ShipmentData;
use Shop\Enums\OrderStatus;
use Shop\Events\OrderShipped;
use Shop\Exceptions\OrderStateException;
use Shop\Models\Order;

final readonly class ShipOrderAction
{
    /**
     * @throws OrderStateException
     */
    public function handle(Order $order, ShipmentData $shipment): Order
    {
        $shipped = DB::transaction(function () use ($order, $shipment): Order {
            $locked = Order::query()->lockForUpdate()->whereKey($order->id)->firstOrFail();

            if (! $locked->isPaid()) {
                throw OrderStateException::cannot('shipped before it is paid', $locked);
            }

            $locked->update([
                'status' => OrderStatus::Shipped,
                'carrier' => $shipment->carrier,
                'tracking_number' => $shipment->trackingNumber,
                'shipped_at' => Date::now(),
            ]);

            return $locked;
        });

        OrderShipped::dispatch($shipped);

        return $shipped;
    }
}
