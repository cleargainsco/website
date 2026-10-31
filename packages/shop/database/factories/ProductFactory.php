<?php

declare(strict_types=1);

namespace Shop\Database\Factories;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Shop\Models\Product;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = Str::title(fake()->unique()->word().' '.fake()->word());

        return [
            'type' => 'simple',
            'name' => $name,
            'slug' => Str::slug($name),
            'summary' => fake()->sentence(),
            'description' => null,
            'details' => [],
            'stock' => 50,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    /**
     * @param  array<int, int>  $unitPricesInMinorUnits  keyed by the quantity each tier starts at
     */
    public function priced(array $unitPricesInMinorUnits = [1 => 2500]): static
    {
        return $this->afterCreating(function (Product $product) use ($unitPricesInMinorUnits): void {
            collect($unitPricesInMinorUnits)->each(fn (int $minor, int $quantity) => $product->prices()->create([
                'currency' => config()->string('shop.currency'),
                'min_quantity' => $quantity,
                'unit_price_minor' => Money::ofMinor($minor, config()->string('shop.currency')),
            ]));
        });
    }
}
