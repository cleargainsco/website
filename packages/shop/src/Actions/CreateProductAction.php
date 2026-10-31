<?php

declare(strict_types=1);

namespace Shop\Actions;

use Illuminate\Support\Facades\DB;
use Shop\Data\ProductData;
use Shop\Models\Product;

final readonly class CreateProductAction
{
    public function __construct(
        private SyncProductPricesAction $syncPrices,
        private SyncProductImageAction $syncImage,
    ) {}

    public function handle(ProductData $data): Product
    {
        $product = DB::transaction(function () use ($data): Product {
            $product = Product::query()->create($data->productAttributes());

            $this->syncPrices->handle($product, $data->prices);

            return $product;
        });

        $this->syncImage->handle($product, $data->image, $data->removeImage);

        return $product;
    }
}
