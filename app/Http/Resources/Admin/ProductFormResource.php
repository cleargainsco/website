<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Shop\Models\Product;
use Shop\Models\ProductPrice;

/**
 * A product as the edit form needs it: prices as the decimal strings the inputs hold.
 *
 * @mixin Product
 */
final class ProductFormResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'type' => $this->type,
            'name' => $this->name,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'details' => (object) $this->details,
            'image_url' => $this->imageUrl(),
            'stock' => $this->stock,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'prices' => $this->prices->map(fn (ProductPrice $price): array => [
                'min_quantity' => $price->min_quantity,
                'unit_price' => (string) $price->unit_price_minor->getAmount(),
            ])->values(),
        ];
    }
}
