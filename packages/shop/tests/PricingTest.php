<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Shop\Contracts\PriceResolver;
use Shop\Exceptions\ProductNotPricedException;
use Shop\Models\Product;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('charges the tier with the highest starting quantity the order reaches', function (int $quantity, int $expectedMinor) {
    $product = Product::factory()->priced([1 => 11200, 3 => 10700, 6 => 9900, 10 => 9200])->create();

    expect(app(PriceResolver::class)->unitPrice($product, $quantity)->getMinorAmount()->toInt())->toBe($expectedMinor);
})->with([
    'one' => [1, 11200],
    'just below a tier' => [5, 10700],
    'on a tier' => [6, 9900],
    'past the last tier' => [40, 9200],
]);

it('refuses a quantity no tier covers', function () {
    $product = Product::factory()->priced([3 => 1000])->create();

    app(PriceResolver::class)->unitPrice($product, 2);
})->throws(ProductNotPricedException::class);
