<?php

declare(strict_types=1);

namespace Shop\Database\Factories;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Shop\Enums\OrderStatus;
use Shop\Models\Order;
use Shop\Models\Product;

/**
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        /** @var class-string<Model> $customer */
        $customer = config('shop.customer_model');

        return [
            'user_id' => fn (): mixed => Factory::factoryForModel($customer)->createOne()->getKey(),
            'number' => '#'.fake()->unique()->numberBetween(10001, 99999),
            'status' => OrderStatus::AwaitingPayment,
            'email' => fake()->safeEmail(),
            'currency' => 'USD',
            'subtotal_minor' => 5000,
            'shipping_minor' => null,
            'tax_minor' => null,
            'total_minor' => 5000,
            'shipping_name' => fake()->name(),
            'shipping_line1' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_region' => 'CA',
            'shipping_postal_code' => fake()->postcode(),
            'shipping_country' => 'United States',
            'details' => [],
            'placed_at' => now(),
        ];
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(['status' => $status]);
    }

    public function forProduct(Product $product, int $quantity = 1, int $unitPriceMinor = 5000): static
    {
        return $this->afterCreating(fn (Order $order) => $order->lines()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'product_details' => $product->details,
            'quantity' => $quantity,
            'currency' => 'USD',
            'unit_price_minor' => Money::ofMinor($unitPriceMinor, 'USD'),
            'line_total_minor' => Money::ofMinor($unitPriceMinor * $quantity, 'USD'),
        ]));
    }
}
