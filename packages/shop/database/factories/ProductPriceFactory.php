<?php

declare(strict_types=1);

namespace Shop\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Shop\Models\Product;
use Shop\Models\ProductPrice;

/**
 * @extends Factory<ProductPrice>
 */
final class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'currency' => 'USD',
            'min_quantity' => 1,
            'unit_price_minor' => 2500,
        ];
    }
}
