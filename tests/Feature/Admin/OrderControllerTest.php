<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\Orders\OrderCancelledNotification;
use App\Notifications\Orders\OrderPaidNotification;
use App\Notifications\Orders\OrderShippedNotification;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Shop\Enums\OrderStatus;
use Shop\Models\Order;

beforeEach(fn () => Notification::fake());

test('admins see every order with what can be done next', function () {
    $order = Order::factory()->forProduct(researchPeptide()->create())->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.orders.index'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/orders/index')->has('orders', 1));

    $this->get(route('admin.orders.show', $order))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/orders/show')
            ->where('order.can_mark_paid', true)
            ->where('order.can_ship', false)
            ->where('order.can_cancel', true));
});

test('an admin marks an order paid, then shipped with tracking, and the customer is told each time', function () {
    $order = Order::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.orders.payment.store', $order));
    $this->post(route('admin.orders.shipment.store', $order), ['carrier' => 'USPS', 'tracking_number' => '9400 1000']);

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Shipped)
        ->and($order->tracking_number)->toBe('9400 1000');

    Notification::assertSentTo($order->customer, OrderPaidNotification::class);
    Notification::assertSentTo($order->customer, OrderShippedNotification::class);
});

test('an order cannot be shipped before it is paid', function () {
    $order = Order::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.orders.shipment.store', $order), ['tracking_number' => 'X']);

    expect($order->refresh()->status)->toBe(OrderStatus::AwaitingPayment);
    Notification::assertNothingSent();
});

test('cancelling an order puts its stock back and tells the customer', function () {
    $product = researchPeptide()->create(['stock' => 0]);
    $order = Order::factory()->forProduct($product, quantity: 3)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.orders.cancellation.store', $order));

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->refresh()->stock)->toBe(3);
    Notification::assertSentTo($order->customer, OrderCancelledNotification::class);
});

test('customers cannot see or change orders in the admin', function () {
    $order = Order::factory()->create();
    $this->actingAs(User::factory()->customer()->create());

    $this->get(route('admin.orders.index'))->assertForbidden();
    $this->get(route('admin.orders.show', $order))->assertForbidden();
    $this->post(route('admin.orders.payment.store', $order))->assertForbidden();
    $this->post(route('admin.orders.shipment.store', $order))->assertForbidden();
    $this->post(route('admin.orders.cancellation.store', $order))->assertForbidden();

    expect($order->refresh()->status)->toBe(OrderStatus::AwaitingPayment);
});
