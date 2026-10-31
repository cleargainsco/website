<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Shop\Models\Order;

test('customers see their own orders, newest first, on the overview and the orders page', function () {
    $customer = User::factory()->customer()->create();
    $older = Order::factory()->for($customer, 'customer')->forProduct(researchPeptide()->create(), 2)->create(['placed_at' => now()->subWeek()]);
    $newer = Order::factory()->for($customer, 'customer')->create(['placed_at' => now()]);
    Order::factory()->create();

    $this->actingAs($customer);

    $this->get(route('account.orders.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('orders', 2)
            ->where('orders.0.id', $newer->public_id)
            ->where('orders.1.id', $older->public_id)
            ->where('orders.1.unit_count', 2));

    $this->get(route('account.index'))
        ->assertInertia(fn (Assert $page) => $page->has('recentOrders', 2));
});

test('a customer can open their own order, and someone else\'s does not exist', function () {
    $customer = User::factory()->customer()->create();
    $mine = Order::factory()->for($customer, 'customer')->forProduct(researchPeptide()->create(['name' => 'Alpha']))->create();
    $theirs = Order::factory()->create();

    $this->actingAs($customer);

    $this->get(route('account.orders.show', ['order' => $mine, 'placed' => 1]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/orders/show')
            ->where('justPlaced', true)
            ->where('order.lines.0.name', 'Alpha'));

    $this->get(route('account.orders.show', $theirs))->assertNotFound();
});
