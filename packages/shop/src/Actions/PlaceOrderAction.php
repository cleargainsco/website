<?php

declare(strict_types=1);

namespace Shop\Actions;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Shop\Cart\Cart;
use Shop\Cart\CartLine;
use Shop\Contracts\ShippingCalculator;
use Shop\Data\CheckoutData;
use Shop\Enums\OrderStatus;
use Shop\Events\OrderPlaced;
use Shop\Exceptions\EmptyCartException;
use Shop\Exceptions\InsufficientStockException;
use Shop\Models\Order;

/**
 * Turns the cart into an order awaiting payment. Stock is taken under a row lock for
 * every line, so the order either reserves everything or nothing.
 */
final readonly class PlaceOrderAction
{
    public function __construct(
        private DecrementStockAction $decrementStock,
        private ShippingCalculator $shipping,
    ) {}

    /**
     * @throws EmptyCartException
     * @throws InsufficientStockException
     */
    public function handle(Model $customer, string $email, CheckoutData $data, Cart $cart): Order
    {
        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            throw new EmptyCartException;
        }

        $currency = config()->string('shop.currency');
        $subtotal = $cart->subtotal($lines) ?? Money::zero($currency);
        $shipping = $this->shipping->quote($lines);

        $order = DB::transaction(function () use ($customer, $email, $data, $lines, $currency, $subtotal, $shipping): Order {
            $lines->each(fn (CartLine $line) => $this->decrementStock->handle($line->product, $line->quantity));

            $order = Order::query()->create([
                'user_id' => $customer->getKey(),
                'status' => OrderStatus::AwaitingPayment,
                'email' => $email,
                'currency' => $currency,
                'subtotal_minor' => $subtotal,
                'shipping_minor' => $shipping,
                'total_minor' => $shipping === null ? $subtotal : $subtotal->plus($shipping),
                'shipping_name' => $data->shippingName,
                'shipping_phone' => $data->shippingPhone,
                'shipping_line1' => $data->shippingLine1,
                'shipping_line2' => $data->shippingLine2,
                'shipping_city' => $data->shippingCity,
                'shipping_region' => $data->shippingRegion,
                'shipping_postal_code' => $data->shippingPostalCode,
                'shipping_country' => $data->shippingCountry,
                'customer_note' => $data->customerNote,
                'details' => $data->details,
                'placed_at' => Date::now(),
            ]);

            $order->update(['number' => config()->string('shop.orders.number_prefix').(10000 + $order->id)]);

            $lines->each(fn (CartLine $line) => $order->lines()->create([
                'product_id' => $line->product->id,
                'product_name' => $line->product->name,
                'product_slug' => $line->product->slug,
                'product_details' => $line->product->details,
                'quantity' => $line->quantity,
                'currency' => $currency,
                'unit_price_minor' => $line->unitPrice,
                'line_total_minor' => $line->total(),
            ]));

            return $order;
        });

        $cart->clear();

        OrderPlaced::dispatch($order);

        return $order;
    }
}
