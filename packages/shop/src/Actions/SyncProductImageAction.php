<?php

declare(strict_types=1);

namespace Shop\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Shop\Models\Product;

/**
 * Stores a product's image, replacing the one before it, or removes it. Each upload is
 * turned upright, scaled down and re-encoded as WebP first, so a phone photo is stored
 * at a fraction of its size.
 */
final readonly class SyncProductImageAction
{
    public function handle(Product $product, ?UploadedFile $image, bool $remove = false): void
    {
        if ($image !== null) {
            $maxDimension = max(1, config()->integer('shop.images.max_dimension'));
            $quality = max(1, min(100, config()->integer('shop.images.quality')));

            $bytes = Image::fromUpload($image)
                ->orient()
                ->scale($maxDimension, $maxDimension)
                ->toWebp()
                ->quality($quality)
                ->toBytes();

            $product->addMediaFromString($bytes)
                ->usingName($product->name)
                ->usingFileName("{$product->slug}.webp")
                ->toMediaCollection(Product::IMAGE_COLLECTION);

            return;
        }

        if ($remove) {
            $product->clearMediaCollection(Product::IMAGE_COLLECTION);
        }
    }
}
