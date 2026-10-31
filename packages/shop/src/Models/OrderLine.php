<?php

declare(strict_types=1);

namespace Shop\Models;

use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shop\Casts\MoneyCast;

/**
 * A product as it was ordered: its name, details and price are copied, so later
 * changes to the product never rewrite an order.
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property string $product_name
 * @property string $product_slug
 * @property array<string, string|null> $product_details
 * @property int $quantity
 * @property string $currency
 * @property Money $unit_price_minor
 * @property Money $line_total_minor
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Order $order
 * @property-read Product|null $product
 */
final class OrderLine extends Model
{
    protected $table = 'shop_order_lines';

    protected $guarded = [];

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'product_id' => 'integer',
            'product_details' => 'array',
            'quantity' => 'integer',
            'unit_price_minor' => MoneyCast::class.':currency',
            'line_total_minor' => MoneyCast::class.':currency',
        ];
    }
}
