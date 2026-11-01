<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Shop\Models\DiscountCode;
use Shop\Models\Order;

beforeEach(function () {
    config(['shop.shipping.flat_rate' => null]);
    $this->customer = User::factory()->customer()->create(['email' => 'ada@example.com']);
    $this->post(route('cart.items.store'), ['product' => researchPeptide()->priced([1 => 5000])->create(['stock' => 5])->public_id, 'quantity' => 2]);
});

test('a customer applies their code and sees the discounted total', function () {
    DiscountCode::factory()->forEmail('ada@example.com')->create(['code' => 'WELCOME-ABC123', 'percent_off' => 10]);

    $this->actingAs($this->customer)
        ->post(route('checkout.discount.store'), ['code' => 'welcome-abc123'])
        ->assertSessionHasNoErrors();

    $this->get(route('checkout.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('discount.code', 'WELCOME-ABC123')
            ->where('discount.percent_off', 10)
            ->where('discount.amount.minor', 1000)
            ->where('total.minor', 9000));
});

test('a code sent to someone else is refused', function () {
    DiscountCode::factory()->forEmail('grace@example.com')->create(['code' => 'GRACE']);

    $this->actingAs($this->customer)
        ->post(route('checkout.discount.store'), ['code' => 'GRACE'])
        ->assertSessionHasErrors(['code' => 'The code GRACE was sent to a different email address.']);
});

test('the order records the discount and the code is spent', function () {
    $code = DiscountCode::factory()->singleUse()->forEmail('ada@example.com')->create(['code' => 'ONCE', 'percent_off' => 20]);

    $this->actingAs($this->customer)->post(route('checkout.discount.store'), ['code' => 'ONCE']);
    $this->post(route('checkout.store'), shippingAddress())->assertSessionHasNoErrors();

    $order = Order::query()->sole();

    expect($order->discount_code)->toBe('ONCE')
        ->and($order->discount_minor?->getMinorAmount()->toInt())->toBe(2000)
        ->and($order->total_minor->getMinorAmount()->toInt())->toBe(8000)
        ->and($code->refresh()->isUsedUp())->toBeTrue();
});

test('a code that stopped working before the order is placed is dropped, and nothing is ordered', function () {
    $code = DiscountCode::factory()->singleUse()->create(['code' => 'ONCE']);
    $this->actingAs($this->customer)->post(route('checkout.discount.store'), ['code' => 'ONCE']);
    $code->update(['times_used' => 1]);

    $this->post(route('checkout.store'), shippingAddress())
        ->assertSessionHasErrors(['code' => 'The code ONCE has already been used.']);

    expect(Order::query()->count())->toBe(0);
    $this->get(route('checkout.create'))->assertInertia(fn (Assert $page) => $page->where('discount', null));
});

test('a customer removes the code', function () {
    DiscountCode::factory()->create(['code' => 'SAVE']);
    $this->actingAs($this->customer)->post(route('checkout.discount.store'), ['code' => 'SAVE']);

    $this->delete(route('checkout.discount.destroy'));

    $this->get(route('checkout.create'))->assertInertia(fn (Assert $page) => $page->where('discount', null));
});
