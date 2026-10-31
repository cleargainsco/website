<?php

declare(strict_types=1);

namespace Shop\Models;

use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Shop\Casts\MoneyCast;
use Shop\Database\Factories\OrderFactory;
use Shop\Enums\OrderStatus;

/**
 * @property int $id
 * @property string $public_id
 * @property string|null $number
 * @property int $user_id
 * @property OrderStatus $status
 * @property string $email
 * @property string $currency
 * @property Money $subtotal_minor
 * @property Money|null $shipping_minor
 * @property Money|null $tax_minor
 * @property Money $total_minor
 * @property string $shipping_name
 * @property string|null $shipping_phone
 * @property string $shipping_line1
 * @property string|null $shipping_line2
 * @property string $shipping_city
 * @property string $shipping_region
 * @property string $shipping_postal_code
 * @property string $shipping_country
 * @property string|null $customer_note
 * @property array<string, mixed> $details
 * @property string|null $carrier
 * @property string|null $tracking_number
 * @property CarbonImmutable $placed_at
 * @property CarbonImmutable|null $paid_at
 * @property CarbonImmutable|null $shipped_at
 * @property CarbonImmutable|null $cancelled_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, OrderLine> $lines
 * @property-read Model $customer
 */
#[UseFactory(OrderFactory::class)]
final class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, HasUlids;

    protected $table = 'shop_orders';

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
     * @return HasMany<OrderLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class)->orderBy('id');
    }

    /**
     * @return BelongsTo<Model, $this>
     */
    public function customer(): BelongsTo
    {
        /** @var class-string<Model> $model */
        $model = config('shop.customer_model');

        return $this->belongsTo($model, 'user_id');
    }

    public function isAwaitingPayment(): bool
    {
        return $this->status === OrderStatus::AwaitingPayment;
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    public function isShipped(): bool
    {
        return $this->status === OrderStatus::Shipped;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::Cancelled;
    }

    public function canBeCancelled(): bool
    {
        return $this->isAwaitingPayment() || $this->isPaid();
    }

    public function unitCount(): int
    {
        return (int) $this->lines->sum('quantity');
    }

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'status' => OrderStatus::class,
            'subtotal_minor' => MoneyCast::class.':currency',
            'shipping_minor' => MoneyCast::class.':currency',
            'tax_minor' => MoneyCast::class.':currency',
            'total_minor' => MoneyCast::class.':currency',
            'details' => 'array',
            'placed_at' => 'immutable_datetime',
            'paid_at' => 'immutable_datetime',
            'shipped_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }
}
