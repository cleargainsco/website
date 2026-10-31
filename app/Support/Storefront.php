<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\StockStatus;
use Illuminate\Support\Collection;

/**
 * @phpstan-type Money array{minor: int, currency: string}
 * @phpstan-type PriceTier array{quantity: int, unit: Money, total: Money}
 * @phpstan-type Batch array{number: string, tested_at: string, purity: string|null, lab: string|null, report_url: string}
 * @phpstan-type Product array{slug: string, name: string, amount: string, summary: string, stock: StockStatus, storage: string, featured: bool, price: Money, tiers: list<PriceTier>, batches: list<Batch>}
 * @phpstan-type ConfiguredBatch array{number: string, tested_at: string, purity?: string, lab?: string, report: string}
 * @phpstan-type ConfiguredProduct array{slug: string, name: string, amount: string, summary: string, stock: string, featured: bool, storage?: string, prices: array<int, int>, batches: list<ConfiguredBatch>}
 */
final class Storefront
{
    /**
     * @return Collection<int, Product>
     */
    public function products(): Collection
    {
        /** @var list<ConfiguredProduct> $products */
        $products = config('storefront.products');

        return collect($products)->map(fn (array $product): array => $this->present($product));
    }

    /**
     * @return Collection<int, Product>
     */
    public function featured(): Collection
    {
        return $this->products()->where('featured', true)->values();
    }

    /**
     * @return Product
     */
    public function find(string $slug): array
    {
        $product = $this->products()->firstWhere('slug', $slug);

        abort_if($product === null, 404);

        return $product;
    }

    /**
     * @param  ConfiguredProduct  $product
     * @return Product
     */
    private function present(array $product): array
    {
        $tiers = array_values(collect($product['prices'])
            ->map(fn (int $unitPrice, int $quantity): array => [
                'quantity' => $quantity,
                'unit' => $this->money($unitPrice),
                'total' => $this->money($unitPrice * $quantity),
            ])
            ->all());

        return [
            'slug' => $product['slug'],
            'name' => $product['name'],
            'amount' => $product['amount'],
            'summary' => $product['summary'],
            'stock' => StockStatus::from($product['stock']),
            'storage' => $product['storage'] ?? config()->string('storefront.storage'),
            'featured' => $product['featured'],
            'price' => $tiers[0]['unit'],
            'tiers' => $tiers,
            'batches' => array_values(collect($product['batches'])
                ->map(fn (array $batch): array => [
                    'number' => $batch['number'],
                    'tested_at' => $batch['tested_at'],
                    'purity' => $batch['purity'] ?? null,
                    'lab' => $batch['lab'] ?? null,
                    'report_url' => asset('coas/'.$batch['report']),
                ])
                ->all()),
        ];
    }

    /**
     * @return Money
     */
    private function money(int $minor): array
    {
        return ['minor' => $minor, 'currency' => config()->string('storefront.currency')];
    }
}
