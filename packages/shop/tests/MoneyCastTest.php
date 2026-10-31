<?php

declare(strict_types=1);

use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shop\Models\Product;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('reads minor units back as money in the row currency', function () {
    $price = Product::factory()->create()->prices()->create([
        'currency' => 'USD',
        'min_quantity' => 1,
        'unit_price_minor' => 1999,
    ]);

    expect($price->refresh()->unit_price_minor->isEqualTo(Money::of('19.99', 'USD')))->toBeTrue();
});

it('refuses money in another currency rather than storing it unconverted', function () {
    Product::factory()->create()->prices()->create([
        'currency' => 'USD',
        'min_quantity' => 1,
        'unit_price_minor' => Money::of('10', 'EUR'),
    ]);
})->throws(InvalidArgumentException::class);
