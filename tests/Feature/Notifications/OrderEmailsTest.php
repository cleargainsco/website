<?php

declare(strict_types=1);

use App\Notifications\Orders\NewOrderNotification;
use App\Notifications\Orders\OrderCancelledNotification;
use App\Notifications\Orders\OrderPaidNotification;
use App\Notifications\Orders\OrderPlacedNotification;
use App\Notifications\Orders\OrderShippedNotification;
use Shop\Enums\OrderStatus;
use Shop\Models\Order;

test('every order email renders from a freshly loaded order', function (string $notification, string $expected) {
    $placed = Order::factory()
        ->forProduct(researchPeptide('10 mg')->create(['name' => 'Alpha']), quantity: 2, unitPriceMinor: 4500)
        ->create(['number' => 'CG-10001', 'status' => OrderStatus::Shipped, 'carrier' => 'USPS', 'tracking_number' => '9400 1000']);
    $order = Order::query()->findOrFail($placed->id);

    $html = (string) (new $notification($order))->toMail($order->customer)->render();

    expect($html)->toContain('CG-10001')->toContain($expected);
})->with([
    'placed' => [OrderPlacedNotification::class, '2 × Alpha 10 mg — $90.00'],
    'new order alert' => [NewOrderNotification::class, 'send them an invoice'],
    'paid' => [OrderPaidNotification::class, 'received payment'],
    'shipped' => [OrderShippedNotification::class, 'USPS 9400 1000'],
    'cancelled' => [OrderCancelledNotification::class, 'has been cancelled'],
]);

test('an order placed with a code shows the discount and the total before shipping', function () {
    $placed = Order::factory()
        ->forProduct(researchPeptide()->create(), quantity: 2, unitPriceMinor: 5000)
        ->create(['subtotal_minor' => 10000, 'discount_code' => 'WELCOME-ABC123', 'discount_minor' => 1000, 'total_minor' => 9000]);
    $order = Order::query()->findOrFail($placed->id);

    $html = (string) (new OrderPlacedNotification($order))->toMail($order->customer)->render();

    expect($html)->toContain('Discount (WELCOME-ABC123):</strong> −$10.00')
        ->toContain('Total before shipping:</strong> $90.00');
});
