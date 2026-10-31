<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\Orders\NewOrderNotification;
use App\Notifications\Orders\OrderPlacedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Shop\Enums\OrderStatus;
use Shop\Models\Order;

function shippingAddress(array $overrides = []): array
{
    return array_replace([
        'shipping_name' => 'Ada Lab',
        'shipping_line1' => '1 Bench St',
        'shipping_city' => 'Austin',
        'shipping_region' => 'TX',
        'shipping_postal_code' => '78701',
        'shipping_country' => 'United States',
        'research_use' => '1',
    ], $overrides);
}

test('checkout needs a signed-in customer with a verified email', function () {
    $this->get(route('checkout.create'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->unverified()->customer()->create())
        ->get(route('checkout.create'))
        ->assertRedirect(route('verification.notice'));
});

test('an empty cart goes back to the cart page', function () {
    $this->actingAs(User::factory()->customer()->create())
        ->get(route('checkout.create'))
        ->assertRedirect(route('cart.show'));
});

test('a customer places an order, which reserves stock and emails them and the shop', function () {
    Notification::fake();
    config(['storefront.order_notifications_email' => 'orders@example.com']);
    $customer = User::factory()->customer()->create(['email' => 'ada@example.com']);
    $product = researchPeptide()->priced([1 => 5000])->create(['stock' => 5]);

    $this->post(route('cart.items.store'), ['product' => $product->public_id, 'quantity' => 2]);
    $this->actingAs($customer)
        ->get(route('checkout.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/checkout')
            ->has('lines', 1)
            ->where('address.shipping_name', $customer->name));

    $response = $this->post(route('checkout.store'), shippingAddress());

    $order = Order::query()->sole();
    $response->assertRedirect(route('account.orders.show', ['order' => $order, 'placed' => 1]));

    expect($order->user_id)->toBe($customer->id)
        ->and($order->email)->toBe('ada@example.com')
        ->and($order->status)->toBe(OrderStatus::AwaitingPayment)
        ->and($order->details)->toHaveKey('research_use_confirmed_at')
        ->and($product->refresh()->stock)->toBe(3);

    Notification::assertSentTo($customer, OrderPlacedNotification::class);
    Notification::assertSentTo(new AnonymousNotifiable, NewOrderNotification::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'orders@example.com');

    $this->get(route('cart.show'))->assertInertia(fn (Assert $page) => $page->where('cartCount', 0));
});

test('the customer must confirm research use and give an address', function () {
    $product = researchPeptide()->priced()->create();
    $this->post(route('cart.items.store'), ['product' => $product->public_id, 'quantity' => 1]);

    $this->actingAs(User::factory()->customer()->create())
        ->post(route('checkout.store'), shippingAddress(['research_use' => null, 'shipping_line1' => '']))
        ->assertSessionHasErrors(['research_use', 'shipping_line1']);

    expect(Order::query()->count())->toBe(0);
});

test('stock that ran out mid-checkout is explained instead of ordered', function () {
    $product = researchPeptide()->priced()->create(['name' => 'Alpha', 'stock' => 2]);
    $this->post(route('cart.items.store'), ['product' => $product->public_id, 'quantity' => 2]);
    $product->update(['stock' => 1]);

    $this->actingAs(User::factory()->customer()->create())
        ->post(route('checkout.store'), shippingAddress())
        ->assertSessionHasErrors(['cart' => 'Only 1 of Alpha left. Lower the quantity in your cart to continue.']);

    expect(Order::query()->count())->toBe(0);
});

test('the next checkout starts from the last address used', function () {
    $customer = User::factory()->customer()->create();
    Order::factory()->for($customer, 'customer')->create(['shipping_line1' => '9 Old Rd', 'placed_at' => now()->subDay()]);
    $product = researchPeptide()->priced()->create();
    $this->post(route('cart.items.store'), ['product' => $product->public_id, 'quantity' => 1]);

    $this->actingAs($customer)
        ->get(route('checkout.create'))
        ->assertInertia(fn (Assert $page) => $page->where('address.shipping_line1', '9 Old Rd'));
});
