<?php

declare(strict_types=1);

namespace Shop\Pricing;

use Brick\Money\Money;
use Shop\Contracts\PriceResolver;
use Shop\Exceptions\ProductNotPricedException;
use Shop\Models\Product;
use Shop\Models\ProductPrice;

/**
 * The unit price is the tier with the highest starting quantity the order reaches.
 */
final class TieredPriceResolver implements PriceResolver
{
    public function unitPrice(Product $product, int $quantity): Money
    {
        $tier = $product->prices
            ->filter(fn (ProductPrice $price): bool => $price->min_quantity <= $quantity)
            ->sortByDesc('min_quantity')
            ->first();

        if ($tier === null) {
            throw ProductNotPricedException::forQuantity($product, $quantity);
        }

        return $tier->unit_price_minor;
    }
}
