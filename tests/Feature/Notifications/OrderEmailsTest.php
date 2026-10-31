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
