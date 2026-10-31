<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Shop\Enums\OrderStatus;
use Shop\Models\Order;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('admins can visit the dashboard', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('dashboard'))->assertOk();
});

test('customers cannot visit the dashboard', function () {
    $this->actingAs(User::factory()->customer()->create());

    $this->get(route('dashboard'))->assertForbidden();
});

describe('figures', function () {
    beforeEach(function () {
        $this->travelTo('2026-10-20 12:00:00');
        $this->actingAs(User::factory()->admin()->create());
    });

    test('revenue counts paid and shipped orders by the day they were paid, against the same point last month', function () {
        Order::factory()->status(OrderStatus::Paid)->create(['total_minor' => 10000, 'paid_at' => '2026-10-05']);
        Order::factory()->status(OrderStatus::Shipped)->create(['total_minor' => 5000, 'paid_at' => '2026-10-10']);
        Order::factory()->status(OrderStatus::Cancelled)->create(['total_minor' => 9900, 'paid_at' => '2026-10-11']);
        Order::factory()->status(OrderStatus::Shipped)->create(['total_minor' => 7000, 'paid_at' => '2026-09-05']);
        Order::factory()->status(OrderStatus::Shipped)->create(['total_minor' => 8000, 'paid_at' => '2026-09-25']);

        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('revenue.this_month', ['minor' => 15000, 'currency' => 'USD'])
                ->where('revenue.last_month_to_date', ['minor' => 7000, 'currency' => 'USD']));
    });

    test('it counts what needs invoicing and shipping, and the customers', function () {
        Order::factory()->create(['total_minor' => 3000]);
        Order::factory()->create(['total_minor' => 2000]);
        Order::factory()->status(OrderStatus::Paid)->create(['paid_at' => now()]);
        User::factory()->customer()->create(['created_at' => '2026-09-01']);
        User::factory()->customer()->create();

        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('awaitingPayment', ['count' => 2, 'value' => ['minor' => 5000, 'currency' => 'USD']])
                ->where('readyToShip', 1)
                ->where('customers', ['total' => 2, 'new_this_month' => 1]));
    });

    test('the chart has every one of the last 30 days, with no gaps', function () {
        Order::factory()->create(['placed_at' => '2026-10-20 09:00:00', 'total_minor' => 4000]);
        Order::factory()->create(['placed_at' => '2026-10-20 10:00:00', 'total_minor' => 1000]);
        Order::factory()->create(['placed_at' => '2026-09-01 10:00:00']);

        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('dailyOrders', 30)
                ->where('dailyOrders.0', ['date' => '2026-09-21', 'orders' => 0, 'value' => ['minor' => 0, 'currency' => 'USD']])
                ->where('dailyOrders.29', ['date' => '2026-10-20', 'orders' => 2, 'value' => ['minor' => 5000, 'currency' => 'USD']]));
    });

    test('products on sale and running low are listed, lowest first', function () {
        config(['shop.low_stock_threshold' => 10]);
        researchPeptide()->create(['name' => 'Alpha', 'stock' => 6]);
        researchPeptide()->create(['name' => 'Beta', 'stock' => 0]);
        researchPeptide()->create(['name' => 'Gamma', 'stock' => 50]);
        researchPeptide()->inactive()->create(['name' => 'Hidden', 'stock' => 1]);

        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('lowStock', 2)
                ->where('lowStock.0.name', 'Beta 10 mg')
                ->where('lowStock.1.stock', 6));
    });
});
