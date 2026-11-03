<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * The home page hero, edited in the admin. There is one row.
 *
 * @property int $id
 * @property string $eyebrow
 * @property string $title
 * @property string|null $title_highlight
 * @property string $description
 * @property string $image_alt
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
final class HomePage extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const string HERO_IMAGE_COLLECTION = 'hero_image';

    public const int HERO_IMAGE_MAX_WIDTH = 1600;

    protected $guarded = [];

    /**
     * The copy defaults live on the columns, so a new row is re-read to pick them up.
     */
    public static function current(): self
    {
        $page = self::query()->with('media')->firstOrCreate();

        return $page->wasRecentlyCreated ? $page->refresh()->load('media') : $page;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::HERO_IMAGE_COLLECTION)->singleFile();
    }

    /**
     * Null until an admin uploads one; the page then shows the bundled photo.
     */
    public function heroImageUrl(): ?string
    {
        return $this->getFirstMediaUrl(self::HERO_IMAGE_COLLECTION) ?: null;
    }

    /**
     * @return array{eyebrow: string, title: string, title_highlight: string|null, description: string, image_url: string|null, image_alt: string}
     */
    public function hero(): array
    {
        return [
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'title_highlight' => $this->title_highlight,
            'description' => $this->description,
            'image_url' => $this->heroImageUrl(),
            'image_alt' => $this->image_alt,
        ];
    }
}
