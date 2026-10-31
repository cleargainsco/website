<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Shop\Actions\RestoreProductAction;
use Shop\Models\Product;

final class ProductRestorationController extends Controller
{
    public function store(Product $product, RestoreProductAction $restoreProduct): RedirectResponse
    {
        $this->authorize('restore', $product);

        $restoreProduct->handle($product);

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name restored.', ['name' => $product->name])]);

        return to_route('admin.products.archived.index');
    }
}
