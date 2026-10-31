<?php

declare(strict_types=1);

namespace Shop\Data;

final readonly class ProductData
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
        public ?string $description,
        public array $details,
        public int $stock,
        public bool $isActive,
        public bool $isFeatured,
        public array $prices,
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
