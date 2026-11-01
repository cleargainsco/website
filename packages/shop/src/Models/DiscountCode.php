<?php

declare(strict_types=1);

namespace Shop\Models;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Shop\Database\Factories\DiscountCodeFactory;
use Shop\Exceptions\DiscountCodeUnavailableException;

/**
 * @property int $id
 * @property string $code
 * @property int $percent_off
 * @property string|null $email
 * @property int|null $usage_limit
 * @property int $times_used
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[UseFactory(DiscountCodeFactory::class)]
final class DiscountCode extends Model
{
    /** @use HasFactory<DiscountCodeFactory> */
    use HasFactory;

    protected $table = 'shop_discount_codes';

    protected $guarded = [];

    public static function normalize(string $code): string
    {
        return mb_strtoupper(trim($code));
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isBefore(Date::now());
    }

    public function isUsedUp(): bool
    {
        return $this->usage_limit !== null && $this->times_used >= $this->usage_limit;
    }

    public function allowsEmail(string $email): bool
    {
        return $this->email === null || $this->email === mb_strtolower($email);
    }

    /**
     * @throws DiscountCodeUnavailableException
     */
    public function ensureRedeemableBy(string $email): void
    {
        throw_if($this->isExpired(), DiscountCodeUnavailableException::expired($this));
        throw_if($this->isUsedUp(), DiscountCodeUnavailableException::usedUp($this));
        throw_unless($this->allowsEmail($email), DiscountCodeUnavailableException::forAnotherCustomer($this));
    }

    public function amountOff(Money $subtotal): Money
    {
        return $subtotal->multipliedBy($this->percent_off)->dividedBy(100, RoundingMode::HalfUp);
    }

    protected function casts(): array
    {
        return [
            'percent_off' => 'integer',
            'usage_limit' => 'integer',
            'times_used' => 'integer',
            'expires_at' => 'immutable_datetime',
        ];
    }
}
