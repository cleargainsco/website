<?php

declare(strict_types=1);

namespace App\Http\Resources\Shop;

use App\Http\Resources\MoneyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Shop\Models\Order;
use Shop\Models\OrderLine;

/**
 * An order with its lines, address and totals, as the customer and the admin both see it.
 *
 * @mixin Order
 */
final class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'number' => $this->number,
            'status' => $this->status,
            'email' => $this->email,
            'placed_at' => $this->placed_at->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'shipped_at' => $this->shipped_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'subtotal' => new MoneyResource($this->subtotal_minor),
            'discount' => $this->discount_minor === null ? null : [
                'code' => $this->discount_code,
                'amount' => new MoneyResource($this->discount_minor),
            ],
            'shipping' => $this->shipping_minor === null ? null : new MoneyResource($this->shipping_minor),
            'tax' => $this->tax_minor === null ? null : new MoneyResource($this->tax_minor),
            'total' => new MoneyResource($this->total_minor),
            'unit_count' => $this->whenLoaded('lines', fn (): int => $this->unitCount()),
            'lines' => $this->whenLoaded('lines', fn () => $this->lines->map(fn (OrderLine $line): array => [
                'name' => $line->product_name,
                'slug' => $line->product_slug,
                'amount' => $line->product_details['amount'] ?? null,
                'quantity' => $line->quantity,
                'unit_price' => new MoneyResource($line->unit_price_minor),
                'total' => new MoneyResource($line->line_total_minor),
            ])->values()),
            'shipping_address' => [
                'name' => $this->shipping_name,
                'phone' => $this->shipping_phone,
                'line1' => $this->shipping_line1,
                'line2' => $this->shipping_line2,
                'city' => $this->shipping_city,
                'region' => $this->shipping_region,
                'postal_code' => $this->shipping_postal_code,
                'country' => $this->shipping_country,
            ],
            'customer_note' => $this->customer_note,
            'carrier' => $this->carrier,
            'tracking_number' => $this->tracking_number,
        ];
    }
}
