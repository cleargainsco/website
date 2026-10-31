<?php

declare(strict_types=1);

namespace Shop\Models;

use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shop\Casts\MoneyCast;
use Shop\Database\Factories\ProductPriceFactory;

/**
 * The unit price that applies from `min_quantity` units of a product upwards.
 *
 * @property int $id
 * @property int $product_id
 * @property int $min_quantity
 * @property Money $unit_price_minor
 * @property string $currency
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Product $product
 */
#[UseFactory(ProductPriceFactory::class)]
final class ProductPrice extends Model
{
    /** @use HasFactory<ProductPriceFactory> */
    use HasFactory;

    protected $table = 'shop_product_prices';

    protected $guarded = [];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'min_quantity' => 'integer',
            'unit_price_minor' => MoneyCast::class.':currency',
        ];
    }
}
