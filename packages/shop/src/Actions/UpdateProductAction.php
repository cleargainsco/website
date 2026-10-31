<?php

declare(strict_types=1);

namespace Shop\Actions;

use Illuminate\Support\Facades\DB;
use Shop\Data\ProductData;
use Shop\Models\Product;

final readonly class UpdateProductAction
{
    public function __construct(
        private SyncProductPricesAction $syncPrices,
        private SyncProductImageAction $syncImage,
    ) {}

    public function handle(Product $product, ProductData $data): Product
    {
        DB::transaction(function () use ($product, $data): void {
            $product->fill($data->productAttributes())->save();

            $this->syncPrices->handle($product, $data->prices);
        });

        $this->syncImage->handle($product, $data->image, $data->removeImage);

        return $product;
    }
}
