<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Collection;
use Shop\Enums\OrderStatus;
use Shop\Models\Order;
use Shop\Models\OrderLine;

/**
 * An order that is never saved, so email previews work before the shop has taken one.
 */
final class SampleOrder
{
    public static function forCustomer(User $user): Order
    {
        $currency = config()->string('shop.currency');

        $lines = new Collection([
            self::line('BPC-157', '10mg', 3, 4500, $currency),
            self::line('TB-500', '5mg', 1, 5200, $currency),
        ]);

        $subtotal = $lines->sum(fn (OrderLine $line): int => $line->line_total_minor->getMinorAmount()->toInt());

        $order = (new Order)->forceFill([
            'currency' => $currency,
            'number' => config()->string('shop.orders.number_prefix').'10042',
            'status' => OrderStatus::AwaitingPayment,
            'email' => $user->email,
            'subtotal_minor' => $subtotal,
            'shipping_minor' => null,
            'tax_minor' => null,
            'total_minor' => $subtotal,
            'shipping_name' => $user->name,
            'shipping_line1' => '100 Research Park Drive',
            'shipping_city' => 'San Diego',
            'shipping_region' => 'CA',
            'shipping_postal_code' => '92121',
            'shipping_country' => 'United States',
            'carrier' => 'USPS',
            'tracking_number' => '9400 1000 0000 0000 0000 00',
            'placed_at' => now(),
        ]);

        $order->public_id = $order->newUniqueId();

        return $order->setRelation('lines', $lines);
    }

    private static function line(string $name, string $amount, int $quantity, int $unitPriceMinor, string $currency): OrderLine
    {
        return (new OrderLine)->forceFill([
            'currency' => $currency,
            'product_name' => $name,
            'product_details' => ['amount' => $amount],
            'quantity' => $quantity,
            'unit_price_minor' => Money::ofMinor($unitPriceMinor, $currency),
            'line_total_minor' => Money::ofMinor($unitPriceMinor * $quantity, $currency),
        ]);
    }
}
