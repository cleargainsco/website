<?php

declare(strict_types=1);

namespace Shop\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class ProductData extends Data
{
    /**
     * @param  array<string, string|null>  $details
     * @param  list<PriceTierData>  $prices
     */
    public function __construct(
        public string $type,
        public string $name,
        public string $slug,
        public string $summary,
        public int $stock,
        #[DataCollectionOf(PriceTierData::class)]
        public array $prices,
        public ?string $description = null,
        public array $details = [],
        public bool $isActive = false,
        public bool $isFeatured = false,
        public ?UploadedFile $image = null,
        public bool $removeImage = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function productAttributes(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'description' => $this->description,
            'details' => $this->details,
            'stock' => $this->stock,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
        ];
    }
}
