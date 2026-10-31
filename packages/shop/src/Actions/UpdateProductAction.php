<?php

declare(strict_types=1);

namespace Shop\Actions;

use Illuminate\Support\Facades\DB;
use Shop\Data\ProductData;
use Shop\Models\Product;

final readonly class UpdateProductAction
{
    public function __construct(private SyncProductPricesAction $syncPrices) {}

    public function handle(Product $product, ProductData $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $product->fill($data->productAttributes())->save();

            $this->syncPrices->handle($product, $data->prices);

            return $product;
        });
    }
}
