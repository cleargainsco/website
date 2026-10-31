<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shop\Cart\Cart;
use Shop\Exceptions\InsufficientStockException;
use Shop\Exceptions\ProductUnavailableException;
use Shop\Models\Product;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('adds up quantities and prices each line at the tier its quantity reaches', function () {
    $product = Product::factory()->priced([1 => 1000, 3 => 900])->create(['stock' => 10]);
    $cart = app(Cart::class);

    $cart->add($product, 1);
    $cart->add($product, 2);

    $line = $cart->lines()->sole();

    expect($cart->count())->toBe(3)
        ->and($line->quantity)->toBe(3)
        ->and($line->unitPrice->getMinorAmount()->toInt())->toBe(900)
        ->and($cart->subtotal($cart->lines())?->getMinorAmount()->toInt())->toBe(2700);
});

it('refuses more than is in stock, and products that are not for sale', function () {
    $cart = app(Cart::class);

    expect(fn () => $cart->add(Product::factory()->priced()->create(['stock' => 2]), 3))
        ->toThrow(InsufficientStockException::class)
        ->and(fn () => $cart->add(Product::factory()->inactive()->priced()->create(), 1))
        ->toThrow(ProductUnavailableException::class)
        ->and(fn () => $cart->add(Product::factory()->create(), 1))
        ->toThrow(ProductUnavailableException::class)
        ->and($cart->isEmpty())->toBeTrue();
});

it('drops a product that has been hidden or archived since it was added', function () {
    $kept = Product::factory()->priced()->create();
    $hidden = Product::factory()->priced()->create();
    $archived = Product::factory()->priced()->create();
    $cart = app(Cart::class);
    collect([$kept, $hidden, $archived])->each(fn (Product $product) => $cart->add($product, 1));

    $hidden->update(['is_active' => false]);
    $archived->delete();

    expect($cart->lines()->map(fn ($line) => $line->product->id)->all())->toBe([$kept->id])
        ->and($cart->count())->toBe(1);
});

it('updates and removes lines', function () {
    $product = Product::factory()->priced()->create();
    $cart = app(Cart::class);

    $cart->add($product, 1);
    $cart->update($product, 4);
    expect($cart->count())->toBe(4);

    $cart->remove($product);
    expect($cart->isEmpty())->toBeTrue();
});
