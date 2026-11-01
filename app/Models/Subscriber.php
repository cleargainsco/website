<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriberSource;
use Carbon\CarbonImmutable;
use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shop\Models\DiscountCode;

/**
 * Someone on the marketing list. Only those with marketing consent are copied to Resend.
 *
 * @property int $id
 * @property string $email
 * @property int|null $user_id
 * @property SubscriberSource $source
 * @property CarbonImmutable|null $marketing_consent_at
 * @property int|null $discount_code_id
 * @property string|null $resend_contact_id
 * @property CarbonImmutable|null $synced_to_resend_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User|null $user
 * @property-read DiscountCode|null $discountCode
 */
final class Subscriber extends Model
{
    /** @use HasFactory<SubscriberFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<DiscountCode, $this>
     */
    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function hasMarketingConsent(): bool
    {
        return $this->marketing_consent_at !== null;
    }

    public function hasWelcomeCode(): bool
    {
        return $this->discount_code_id !== null;
    }

    public function isSyncedToResend(): bool
    {
        return $this->synced_to_resend_at !== null;
    }

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'source' => SubscriberSource::class,
            'marketing_consent_at' => 'immutable_datetime',
            'discount_code_id' => 'integer',
            'synced_to_resend_at' => 'immutable_datetime',
        ];
    }
}
