<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Http\Resources\Shop\OrderResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Shop\Models\Order;

/**
 * @mixin Order
 */
final class AdminOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...(new OrderResource($this->resource))->toArray($request),
            'customer_name' => $this->whenLoaded('customer', fn (): ?string => $this->customer instanceof User ? $this->customer->name : null),
            'research_use_confirmed_at' => $this->details['research_use_confirmed_at'] ?? null,
            'can_mark_paid' => $this->isAwaitingPayment(),
            'can_ship' => $this->isPaid(),
            'can_cancel' => $this->canBeCancelled(),
        ];
    }
}
