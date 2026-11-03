<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Http\Resources\MoneyResource;
use App\Models\User;
use Brick\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

/**
 * A customer with their order count, what they have spent on paid orders and whether
 * they are on the marketing list. Expects withCount('orders'), withSum on paid totals
 * as spent_minor, withMax('orders', 'placed_at') and the subscriber relation.
 *
 * @mixin User
 */
final class CustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => $this->email_verified_at !== null,
            'joined_at' => $this->created_at?->toIso8601String(),
            'orders_count' => (int) $this->orders_count,
            'spent' => new MoneyResource(Money::ofMinor((int) ($this->spent_minor ?? 0), config()->string('shop.currency'))),
            'last_order_at' => $this->orders_max_placed_at === null ? null : Date::parse($this->orders_max_placed_at)->toIso8601String(),
            'marketing_consent' => $this->whenLoaded('subscriber', fn (): bool => $this->subscriber?->hasMarketingConsent() ?? false),
        ];
    }
}
