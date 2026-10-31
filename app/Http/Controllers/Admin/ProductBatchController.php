<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Products\AddProductBatchAction;
use App\Data\ProductBatchData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductBatchRequest;
use App\Models\ProductBatch;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Shop\Models\Product;

final class ProductBatchController extends Controller
{
    public function store(StoreProductBatchRequest $request, Product $product, AddProductBatchAction $addBatch): RedirectResponse
    {
        $batch = $addBatch->handle($product, ProductBatchData::from($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Batch :number added.', ['number' => $batch->number])]);

        return back();
    }

    public function destroy(Product $product, ProductBatch $batch): RedirectResponse
    {
        $this->authorize('update', $product);

        $batch->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Batch :number removed.', ['number' => $batch->number])]);

        return back();
    }
}
