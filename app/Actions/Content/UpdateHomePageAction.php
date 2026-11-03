<?php

declare(strict_types=1);

namespace App\Actions\Content;

use App\Data\HomePageData;
use App\Models\HomePage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Image;

final readonly class UpdateHomePageAction
{
    public function handle(HomePageData $data): HomePage
    {
        return DB::transaction(function () use ($data): HomePage {
            $page = HomePage::current();

            $page->fill([
                'eyebrow' => $data->eyebrow,
                'title' => $data->title,
                'title_highlight' => $data->titleHighlight,
                'description' => $data->description,
                'image_alt' => $data->imageAlt,
            ])->save();

            if ($data->heroImage !== null) {
                $bytes = Image::fromUpload($data->heroImage)
                    ->orient()
                    ->scale(width: HomePage::HERO_IMAGE_MAX_WIDTH)
                    ->toWebp()
                    ->quality(82)
                    ->toBytes();

                $page->addMediaFromString($bytes)
                    ->usingFileName('home-hero.webp')
                    ->toMediaCollection(HomePage::HERO_IMAGE_COLLECTION);
            } elseif ($data->removeHeroImage) {
                $page->clearMediaCollection(HomePage::HERO_IMAGE_COLLECTION);
            }

            return $page;
        });
    }
}
