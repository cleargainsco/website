<?php

declare(strict_types=1);

namespace Shop\Actions;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Shop\Enums\OrderStatus;
use Shop\Events\OrderCancelled;
use Shop\Exceptions\OrderStateException;
use Shop\Models\DiscountCode;
use Shop\Models\Order;
use Shop\Models\OrderLine;

/**
 * Cancels an order that has not shipped, and puts its stock and any discount code use back.
 */
final readonly class CancelOrderAction
{
    public function __construct(private IncrementStockAction $incrementStock) {}

    /**
     * @throws OrderStateException
     */
    public function handle(Order $order): Order
    {
        $cancelled = DB::transaction(function () use ($order): Order {
            $locked = Order::query()->lockForUpdate()->with('lines.product')->whereKey($order->id)->firstOrFail();

            if (! $locked->canBeCancelled()) {
                throw OrderStateException::cannot('cancelled', $locked);
            }

            $locked->lines
                ->filter(fn (OrderLine $line): bool => $line->product !== null)
                ->each(fn (OrderLine $line) => $this->incrementStock->handle($line->product, $line->quantity));

            if ($locked->discount_code !== null) {
                DiscountCode::query()
                    ->where('code', $locked->discount_code)
                    ->where('times_used', '>', 0)
                    ->decrement('times_used');
            }

            $locked->update(['status' => OrderStatus::Cancelled, 'cancelled_at' => Date::now()]);

            return $locked;
        });

        OrderCancelled::dispatch($cancelled);

        return $cancelled;
    }
}
