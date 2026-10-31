<?php

declare(strict_types=1);

namespace App\Actions\Seo;

use App\Data\SeoPageData;
use App\Enums\SeoPage;
use App\Models\SeoPageSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Image;

/**
 * A share image is cropped to the 1200×630 that link previews display, and stored as
 * JPEG because not every preview renders WebP.
 */
final readonly class UpdateSeoPageAction
{
    public function handle(SeoPage $page, SeoPageData $data): SeoPageSetting
    {
        return DB::transaction(function () use ($page, $data): SeoPageSetting {
            $setting = SeoPageSetting::query()->updateOrCreate(
                ['page' => $page],
                ['title' => $data->title, 'description' => $data->description],
            );

            if ($data->shareImage !== null) {
                $bytes = Image::fromUpload($data->shareImage)
                    ->orient()
                    ->cover(SeoPageSetting::SHARE_IMAGE_WIDTH, SeoPageSetting::SHARE_IMAGE_HEIGHT)
                    ->toJpeg()
                    ->quality(85)
                    ->toBytes();

                $setting->addMediaFromString($bytes)
                    ->usingFileName(str_replace('.', '-', $page->value).'-share.jpg')
                    ->toMediaCollection(SeoPageSetting::SHARE_IMAGE_COLLECTION);
            } elseif ($data->removeShareImage) {
                $setting->clearMediaCollection(SeoPageSetting::SHARE_IMAGE_COLLECTION);
            }

            return $setting;
        });
    }
}
