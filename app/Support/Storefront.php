<?php

declare(strict_types=1);

namespace App\Support;

use App\Handlers\ProductTypes\ResearchPeptideProductType;
use Brick\Money\Money as BrickMoney;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Shop\Enums\StockStatus;
use Shop\Models\Product as ShopProduct;
use Shop\Models\ProductPrice;

/**
 * @phpstan-type Money array{minor: int, currency: string}
 * @phpstan-type PriceTier array{quantity: int, unit: Money, total: Money}
 * @phpstan-type Batch array{number: string, tested_at: string, purity: string|null, lab: string|null, report_url: string}
 * @phpstan-type Product array{slug: string, name: string, amount: string, summary: string, image_url: string|null, stock: StockStatus, storage: string, featured: bool, price: Money, tiers: list<PriceTier>, batches: list<Batch>}
 * @phpstan-type ConfiguredBatch array{number: string, tested_at: string, purity?: string, lab?: string, report: string}
 */
final class Storefront
{
    /**
     * @return Collection<int, Product>
     */
    public function products(): Collection
    {
        return $this->query()->get()->map(fn (ShopProduct $product): array => $this->present($product));
    }

    /**
     * @return Collection<int, Product>
     */
    public function featured(): Collection
    {
        return $this->query()->featured()->get()->map(fn (ShopProduct $product): array => $this->present($product));
    }

    /**
     * @return Product
     */
    public function find(string $slug): array
    {
        return $this->present($this->query()->where('slug', $slug)->firstOrFail());
    }

    /**
     * The storefront is designed around research peptides, so it lists that type only.
     * Products without a price are left out: there is nothing to charge for them yet.
     *
     * @return Builder<ShopProduct>
     */
    private function query(): Builder
    {
        return ShopProduct::query()
            ->where('type', ResearchPeptideProductType::KEY)
            ->active()
            ->whereHas('prices')
            ->with(['prices', 'media'])
            ->ordered();
    }

    /**
     * @return Product
     */
    private function present(ShopProduct $product): array
    {
        $tiers = array_values($product->prices
            ->map(fn (ProductPrice $price): array => [
                'quantity' => $price->min_quantity,
                'unit' => $this->money($price->unit_price_minor),
                'total' => $this->money($price->unit_price_minor->multipliedBy($price->min_quantity)),
            ])
            ->all());

        /** @var list<ConfiguredBatch> $batches */
        $batches = config("storefront.batches.{$product->slug}", []);

        return [
            'slug' => $product->slug,
            'name' => $product->name,
            'amount' => $product->details['amount'] ?? '',
            'summary' => $product->summary,
            'image_url' => $product->imageUrl(),
            'stock' => $product->stockStatus(),
            'storage' => $product->details['storage'] ?? config()->string('storefront.storage'),
            'featured' => $product->is_featured,
            'price' => $tiers[0]['unit'],
            'tiers' => $tiers,
            'batches' => array_values(collect($batches)
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
    private function money(BrickMoney $money): array
    {
        return ['minor' => $money->getMinorAmount()->toInt(), 'currency' => $money->getCurrency()->getCurrencyCode()];
    }
}
