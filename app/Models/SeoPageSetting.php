<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SeoPage;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * What an admin has changed about a page's search and share metadata. Anything left
 * empty falls back to the page's default.
 *
 * @property int $id
 * @property SeoPage $page
 * @property string|null $title
 * @property string|null $description
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
final class SeoPageSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const string SHARE_IMAGE_COLLECTION = 'share_image';

    public const int SHARE_IMAGE_WIDTH = 1200;

    public const int SHARE_IMAGE_HEIGHT = 630;

    protected $guarded = [];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::SHARE_IMAGE_COLLECTION)->singleFile();
    }

    /**
     * Load `media` with the setting: models are strict, so reading it lazily throws.
     */
    public function shareImageUrl(): ?string
    {
        return $this->getFirstMediaUrl(self::SHARE_IMAGE_COLLECTION) ?: null;
    }

    protected function casts(): array
    {
        return [
            'page' => SeoPage::class,
        ];
    }
}
