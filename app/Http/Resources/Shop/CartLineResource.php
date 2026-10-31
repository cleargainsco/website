<?php

declare(strict_types=1);

namespace App\Http\Resources\Shop;

use App\Http\Resources\MoneyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Shop\Cart\CartLine;

/**
 * @mixin CartLine
 */
final class CartLineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product->public_id,
            'slug' => $this->product->slug,
            'name' => $this->product->name,
            'amount' => $this->product->details['amount'] ?? null,
            'image_url' => $this->product->thumbnailUrl(),
            'quantity' => $this->quantity,
            'stock' => $this->product->stock,
            'unit_price' => new MoneyResource($this->unitPrice),
            'total' => new MoneyResource($this->total()),
        ];
    }
}
