<?php

declare(strict_types=1);

namespace Shop\Models;

use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Shop\Database\Factories\ProductFactory;
use Shop\Enums\StockStatus;

/**
 * @property int $id
 * @property string $public_id
 * @property string $type
 * @property string $name
 * @property string $slug
 * @property string $summary
 * @property string|null $description
 * @property array<string, string|null> $details
 * @property int $stock
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $sort_order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, ProductPrice> $prices
 */
#[UseFactory(ProductFactory::class)]
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasUlids;

    protected $table = 'shop_products';

    protected $guarded = [];

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * @return HasMany<ProductPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class)->orderBy('min_quantity');
    }

    public function stockStatus(): StockStatus
    {
        return StockStatus::forCount($this->stock, config()->integer('shop.low_stock_threshold'));
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function basePrice(): ?Money
    {
        return $this->prices->first()?->unit_price_minor;
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name')->orderBy('id');
    }

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
