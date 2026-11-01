<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shop\Actions\CancelOrderAction;
use Shop\Actions\PlaceOrderAction;
use Shop\Cart\Cart;
use Shop\Data\CheckoutData;
use Shop\Exceptions\DiscountCodeUnavailableException;
use Shop\Models\DiscountCode;
use Shop\Models\Order;
use Shop\Models\Product;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function discountCheckout(): CheckoutData
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

function cartWith(Product $product, int $quantity): Cart
{
    $cart = app(Cart::class);
    $cart->add($product, $quantity);

    return $cart;
}

describe('applying a code to the cart', function () {
    it('finds the code whatever case it is typed in', function () {
        DiscountCode::factory()->create(['code' => 'WELCOME-ABC123']);
        $cart = app(Cart::class);

        $cart->applyDiscount(' welcome-abc123 ', 'ada@example.com');

        expect($cart->discountCode())->toBe('WELCOME-ABC123');
    });

    it('refuses a code that does not exist, has expired, is used up or was sent to someone else', function (Closure $makeCode, string $message) {
        $code = $makeCode()?->code ?? 'NOPE';

        expect(fn () => app(Cart::class)->applyDiscount($code, 'ada@example.com'))
            ->toThrow(DiscountCodeUnavailableException::class, $message);
    })->with([
        'unknown' => [fn () => null, 'find the code'],
        'expired' => [fn () => DiscountCode::factory()->expired()->create(), 'has expired'],
        'used up' => [fn () => DiscountCode::factory()->singleUse()->create(['times_used' => 1]), 'already been used'],
        'another email' => [fn () => DiscountCode::factory()->forEmail('grace@example.com')->create(), 'different email'],
    ]);

    it('forgets the code with the rest of the cart', function () {
        DiscountCode::factory()->create(['code' => 'SAVE']);
        $cart = app(Cart::class);
        $cart->applyDiscount('SAVE', 'ada@example.com');

        $cart->clear();

        expect($cart->discountCode())->toBeNull();
    });
});

describe('placing an order with a code', function () {
    it('takes the percentage off the subtotal before shipping and counts the use', function () {
        config(['shop.shipping.flat_rate' => '9.50']);
        $code = DiscountCode::factory()->singleUse()->forEmail('ada@example.com')->create(['code' => 'TEN', 'percent_off' => 10]);
        $cart = cartWith(Product::factory()->priced([1 => 1995])->create(['stock' => 5]), 3);
        $cart->applyDiscount('TEN', 'ada@example.com');

        $order = app(PlaceOrderAction::class)->handle(User::factory()->create(), 'ada@example.com', discountCheckout(), $cart);

        expect($order->subtotal_minor->getMinorAmount()->toInt())->toBe(5985)
            ->and($order->discount_code)->toBe('TEN')
            ->and($order->discount_minor?->getMinorAmount()->toInt())->toBe(599)
            ->and($order->total_minor->getMinorAmount()->toInt())->toBe(5985 - 599 + 950)
            ->and($code->refresh()->times_used)->toBe(1);
    });

    it('places nothing when the code was spent on another order in the meantime', function () {
        $product = Product::factory()->priced([1 => 1000])->create(['stock' => 5]);
        $code = DiscountCode::factory()->singleUse()->create(['code' => 'ONCE']);
        $cart = cartWith($product, 2);
        $cart->applyDiscount('ONCE', 'ada@example.com');
        $code->update(['times_used' => 1]);

        expect(fn () => app(PlaceOrderAction::class)->handle(User::factory()->create(), 'ada@example.com', discountCheckout(), $cart))
            ->toThrow(DiscountCodeUnavailableException::class);

        expect(Order::query()->count())->toBe(0)
            ->and($product->refresh()->stock)->toBe(5);
    });

    it('gives the use back when the order is cancelled', function () {
        $code = DiscountCode::factory()->singleUse()->create(['code' => 'ONCE']);
        $cart = cartWith(Product::factory()->priced([1 => 1000])->create(['stock' => 5]), 1);
        $cart->applyDiscount('ONCE', 'ada@example.com');
        $order = app(PlaceOrderAction::class)->handle(User::factory()->create(), 'ada@example.com', discountCheckout(), $cart);

        app(CancelOrderAction::class)->handle($order);

        expect($code->refresh()->times_used)->toBe(0);
    });
});
