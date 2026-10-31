<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Data\ProductBatchData;
use App\Models\ProductBatch;
use Shop\Models\Product;

final readonly class AddProductBatchAction
{
    public function handle(Product $product, ProductBatchData $data): ProductBatch
    {
        $batch = ProductBatch::query()->create([
            'product_id' => $product->id,
            'number' => $data->number,
            'tested_at' => $data->testedAt,
            'purity' => $data->purity,
            'lab' => $data->lab,
        ]);

        $batch->addMedia($data->certificate)
            ->usingFileName("{$product->slug}-{$batch->number}-coa.pdf")
            ->toMediaCollection(ProductBatch::CERTIFICATE_COLLECTION);

        return $batch;
    }
}
