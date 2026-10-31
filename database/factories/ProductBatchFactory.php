<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductBatch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Shop\Models\Product;

/**
 * @extends Factory<ProductBatch>
 */
final class ProductBatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'number' => strtoupper(fake()->unique()->bothify('CG-####-??')),
            'tested_at' => fake()->dateTimeBetween('-6 months')->format('Y-m-d'),
            'purity' => fake()->randomFloat(1, 98, 99.9).'%',
            'lab' => fake()->company(),
        ];
    }
}
