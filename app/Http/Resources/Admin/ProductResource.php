<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Http\Resources\MoneyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Shop\Models\Product;
use Shop\ProductTypeRegistry;

/**
 * @mixin Product
 */
final class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $basePrice = $this->basePrice();

        return [
            'id' => $this->public_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type_label' => app(ProductTypeRegistry::class)->get($this->type)->label(),
            'amount' => $this->details['amount'] ?? null,
            'stock' => $this->stock,
            'stock_status' => $this->stockStatus(),
            'base_price' => $basePrice === null ? null : new MoneyResource($basePrice),
            'tier_count' => $this->prices->count(),
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
        ];
    }
}
