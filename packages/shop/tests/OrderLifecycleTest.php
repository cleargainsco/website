<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Shop\Actions\CancelOrderAction;
use Shop\Actions\MarkOrderPaidAction;
use Shop\Actions\PlaceOrderAction;
use Shop\Actions\ShipOrderAction;
use Shop\Cart\Cart;
use Shop\Contracts\OrderHistory;
use Shop\Data\CheckoutData;
use Shop\Data\ShipmentData;
use Shop\Enums\OrderStatus;
use Shop\Events\OrderCancelled;
use Shop\Events\OrderPaid;
use Shop\Events\OrderPlaced;
use Shop\Events\OrderShipped;
use Shop\Exceptions\EmptyCartException;
use Shop\Exceptions\InsufficientStockException;
use Shop\Exceptions\OrderStateException;
use Shop\Models\Order;
use Shop\Models\Product;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function checkoutData(): CheckoutData
{
    return CheckoutData::from([
        'shipping_name' => 'Ada Lab',
        'shipping_line1' => '1 Bench St',
        'shipping_city' => 'Austin',
        'shipping_region' => 'TX',
        'shipping_postal_code' => '78701',
        'shipping_country' => 'United States',
    ]);
}

it('turns the cart into an order awaiting payment, takes the stock and copies the prices', function () {
    Event::fake([OrderPlaced::class]);
    config(['shop.orders.number_prefix' => 'T-', 'shop.shipping.flat_rate' => '9.50']);
    $product = Product::factory()->priced([1 => 1000, 3 => 900])->create(['name' => 'Alpha', 'stock' => 5, 'details' => ['amount' => '10 mg']]);
    $cart = app(Cart::class);
    $cart->add($product, 3);

    $order = app(PlaceOrderAction::class)->handle(User::factory()->create(), 'ada@example.com', checkoutData(), $cart);

    $line = $order->lines()->sole();

    expect($order->status)->toBe(OrderStatus::AwaitingPayment)
        ->and($order->number)->toBe('T-'.(10000 + $order->id))
        ->and($order->subtotal_minor->getMinorAmount()->toInt())->toBe(2700)
        ->and($order->shipping_minor?->getMinorAmount()->toInt())->toBe(950)
        ->and($order->total_minor->getMinorAmount()->toInt())->toBe(3650)
        ->and($line->product_name)->toBe('Alpha')
        ->and($line->product_details)->toBe(['amount' => '10 mg'])
        ->and($line->unit_price_minor->getMinorAmount()->toInt())->toBe(900)
        ->and($product->refresh()->stock)->toBe(2)
        ->and($cart->isEmpty())->toBeTrue()
        ->and(app(OrderHistory::class)->hasBeenOrdered($product))->toBeTrue();

    Event::assertDispatched(OrderPlaced::class);
});

it('leaves shipping to the invoice when no flat rate is set', function () {
    config(['shop.shipping.flat_rate' => null]);
    $cart = app(Cart::class);
    $cart->add(Product::factory()->priced([1 => 1000])->create(), 2);

    $order = app(PlaceOrderAction::class)->handle(User::factory()->create(), 'ada@example.com', checkoutData(), $cart);

    expect($order->shipping_minor)->toBeNull()
        ->and($order->total_minor->getMinorAmount()->toInt())->toBe(2000);
});

it('reserves everything or nothing when one line is short', function () {
    $plenty = Product::factory()->priced()->create(['stock' => 10]);
    $scarce = Product::factory()->priced()->create(['stock' => 2]);
    $cart = app(Cart::class);
    $cart->add($plenty, 4);
    $cart->add($scarce, 2);
    $scarce->update(['stock' => 1]);

    expect(fn () => app(PlaceOrderAction::class)->handle(User::factory()->create(), 'ada@example.com', checkoutData(), $cart))
        ->toThrow(InsufficientStockException::class);

    expect($plenty->refresh()->stock)->toBe(10)
        ->and(Order::query()->count())->toBe(0)
        ->and($cart->count())->toBe(6);
});

it('refuses an empty cart', function () {
    app(PlaceOrderAction::class)->handle(User::factory()->create(), 'ada@example.com', checkoutData(), app(Cart::class));
})->throws(EmptyCartException::class);

it('moves an order from paid to shipped, and only in that order', function () {
    Event::fake([OrderPaid::class, OrderShipped::class]);
    $order = Order::factory()->create();

    expect(fn () => app(ShipOrderAction::class)->handle($order, new ShipmentData))->toThrow(OrderStateException::class);

    app(MarkOrderPaidAction::class)->handle($order);
    expect(fn () => app(MarkOrderPaidAction::class)->handle($order))->toThrow(OrderStateException::class);

    $shipped = app(ShipOrderAction::class)->handle($order, new ShipmentData('USPS', '9400 1000'));

    expect($shipped->status)->toBe(OrderStatus::Shipped)
        ->and($shipped->paid_at)->not->toBeNull()
        ->and($shipped->tracking_number)->toBe('9400 1000');

    Event::assertDispatched(OrderPaid::class);
    Event::assertDispatched(OrderShipped::class);
});

it('puts the stock back when an order is cancelled, but never cancels a shipped one', function () {
    Event::fake([OrderCancelled::class]);
    $product = Product::factory()->priced()->create(['stock' => 1]);
    $order = Order::factory()->forProduct($product, quantity: 4)->create();

    app(CancelOrderAction::class)->handle($order);

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->refresh()->stock)->toBe(5);

    Event::assertDispatched(OrderCancelled::class);

    app(CancelOrderAction::class)->handle(Order::factory()->status(OrderStatus::Shipped)->create());
})->throws(OrderStateException::class);
