<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shop\Actions\DecrementStockAction;
use Shop\Enums\StockStatus;
use Shop\Exceptions\InsufficientStockException;
use Shop\Models\Product;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('works out the stock status from the count', function (int $stock, StockStatus $status) {
    config(['shop.low_stock_threshold' => 10]);

    expect(Product::factory()->make(['stock' => $stock])->stockStatus())->toBe($status);
})->with([
    'none left' => [0, StockStatus::OutOfStock],
    'at the threshold' => [10, StockStatus::LowStock],
    'above it' => [11, StockStatus::InStock],
]);

it('takes stock off the count', function () {
    $product = Product::factory()->create(['stock' => 5]);

    $updated = app(DecrementStockAction::class)->handle($product, 3);

    expect($updated->stock)->toBe(2)
        ->and($product->refresh()->stock)->toBe(2);
});

it('refuses to take more than is left and leaves the count alone', function () {
    $product = Product::factory()->create(['stock' => 2]);

    expect(fn () => app(DecrementStockAction::class)->handle($product, 3))
        ->toThrow(InsufficientStockException::class);

    expect($product->refresh()->stock)->toBe(2);
});

it('checks the stored count, not a stale copy of the product', function () {
    $product = Product::factory()->create(['stock' => 3]);
    $stale = Product::query()->findOrFail($product->id);

    app(DecrementStockAction::class)->handle($product, 3);

    expect(fn () => app(DecrementStockAction::class)->handle($stale, 1))
        ->toThrow(InsufficientStockException::class);
});

it('refuses a quantity below one', function () {
    app(DecrementStockAction::class)->handle(Product::factory()->create(), 0);
})->throws(InvalidArgumentException::class);
