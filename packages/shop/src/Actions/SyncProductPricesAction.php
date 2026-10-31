<?php

declare(strict_types=1);

namespace Shop\Actions;

use Shop\Data\PriceTierData;
use Shop\Models\Product;

/**
 * Replaces a product's price tiers. Orders copy the price they were charged, so no
 * history depends on the old rows.
 */
final readonly class SyncProductPricesAction
{
    /**
     * @param  list<PriceTierData>  $prices
     */
    public function handle(Product $product, array $prices): void
    {
        $product->prices()->delete();

        collect($prices)->each(fn (PriceTierData $tier) => $product->prices()->create([
            'currency' => $tier->unitPrice->getCurrency()->getCurrencyCode(),
            'min_quantity' => $tier->minQuantity,
            'unit_price_minor' => $tier->unitPrice,
        ]));

        $product->unsetRelation('prices');
    }
}
