<?php

declare(strict_types=1);

use App\Models\Subscriber;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Shop\Enums\OrderStatus;
use Shop\Models\DiscountCode;
use Shop\Models\Order;

describe('the customer list', function () {
    test('shows customers with their orders, what they have spent and whether they subscribed', function () {
        $ada = User::factory()->customer()->create(['name' => 'Ada Lovelace']);
        Order::factory()->for($ada, 'customer')->status(OrderStatus::Paid)->create(['total_minor' => 5000, 'placed_at' => '2026-10-01 10:00:00']);
        Order::factory()->for($ada, 'customer')->status(OrderStatus::Shipped)->create(['total_minor' => 2500, 'placed_at' => '2026-10-20 10:00:00']);
        Order::factory()->for($ada, 'customer')->status(OrderStatus::AwaitingPayment)->create(['total_minor' => 9900, 'placed_at' => '2026-10-25 10:00:00']);
        Subscriber::factory()->create(['email' => $ada->email, 'user_id' => $ada->id]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.customers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/customers/index')
                ->has('customers.data', 1)
                ->where('customers.data.0.name', 'Ada Lovelace')
                ->where('customers.data.0.orders_count', 3)
                ->where('customers.data.0.spent.minor', 7500)
                ->where('customers.data.0.last_order_at', '2026-10-25T10:00:00+00:00')
                ->where('customers.data.0.marketing_consent', true));
    });

    test('leaves admins out', function () {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.customers.index'))
            ->assertInertia(fn (Assert $page) => $page->where('customers.total', 0));
    });

    test('searches by name or email', function () {
        User::factory()->customer()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
        User::factory()->customer()->create(['name' => 'Grace Hopper', 'email' => 'grace@navy.test']);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.customers.index', ['search' => 'navy']))
            ->assertInertia(fn (Assert $page) => $page->has('customers.data', 1)->where('customers.data.0.name', 'Grace Hopper'));

        $this->actingAs($admin)
            ->get(route('admin.customers.index', ['search' => 'lovelace']))
            ->assertInertia(fn (Assert $page) => $page->has('customers.data', 1)->where('search', 'lovelace'));
    });
});

describe('a customer', function () {
    test('shows their orders and their welcome code', function () {
        $ada = User::factory()->customer()->create();
        Order::factory()->for($ada, 'customer')->create(['number' => 'CG-10001']);
        Order::factory()->create(['number' => 'CG-10002']);
        $code = DiscountCode::factory()->singleUse()->create(['code' => 'ABC234', 'times_used' => 1]);
        Subscriber::factory()->create(['email' => $ada->email, 'user_id' => $ada->id, 'discount_code_id' => $code->id]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.customers.show', $ada))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/customers/show')
                ->where('customer.id', $ada->id)
                ->has('orders', 1)
                ->where('orders.0.number', 'CG-10001')
                ->where('welcome_code', ['code' => 'ABC234', 'used' => true, 'expired' => false]));
    });

    test('an admin account is not a customer page', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.customers.show', $admin))->assertNotFound();
    });
});

test('customers cannot see the customer pages', function () {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)->get(route('admin.customers.index'))->assertForbidden();
    $this->actingAs($customer)->get(route('admin.customers.show', $customer))->assertForbidden();
});
