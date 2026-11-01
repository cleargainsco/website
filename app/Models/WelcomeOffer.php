<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * The first-visit discount and the popup copy, edited in the admin. There is one row.
 *
 * @property int $id
 * @property bool $is_enabled
 * @property int $percent_off
 * @property int|null $valid_days
 * @property string $eyebrow
 * @property string $headline
 * @property string $body
 * @property string $button_label
 * @property string $success_headline
 * @property string $success_body
 * @property string $fine_print
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
final class WelcomeOffer extends Model
{
    protected $guarded = [];

    /**
     * The copy defaults live on the columns, so a new row is re-read to pick them up.
     */
    public static function current(): self
    {
        $offer = self::query()->firstOrCreate();

        return $offer->wasRecentlyCreated ? $offer->refresh() : $offer;
    }

    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    /**
     * @return array{eyebrow: string, headline: string, body: string, button_label: string, success_headline: string, success_body: string, fine_print: string}
     */
    public function copy(): array
    {
        return [
            'eyebrow' => $this->eyebrow,
            'headline' => $this->headline,
            'body' => $this->body,
            'button_label' => $this->button_label,
            'success_headline' => $this->success_headline,
            'success_body' => $this->success_body,
            'fine_print' => $this->fine_print,
        ];
    }

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'percent_off' => 'integer',
            'valid_days' => 'integer',
        ];
    }
}
