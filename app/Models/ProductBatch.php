<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ProductBatchFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shop\Models\Product;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * One tested production batch of a product, with its certificate of analysis.
 *
 * @property int $id
 * @property string $public_id
 * @property int $product_id
 * @property string $number
 * @property CarbonImmutable $tested_at
 * @property string|null $purity
 * @property string|null $lab
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Product $product
 */
final class ProductBatch extends Model implements HasMedia
{
    /** @use HasFactory<ProductBatchFactory> */
    use HasFactory, HasUlids, InteractsWithMedia;

    public const string CERTIFICATE_COLLECTION = 'certificate';

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
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::CERTIFICATE_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf']);
    }

    /**
     * Load `media` with the batch: models are strict, so reading it lazily throws.
     */
    public function certificateUrl(): ?string
    {
        return $this->getFirstMediaUrl(self::CERTIFICATE_COLLECTION) ?: null;
    }

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'tested_at' => 'immutable_date',
        ];
    }
}
