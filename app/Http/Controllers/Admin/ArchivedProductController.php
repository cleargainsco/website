<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductResource;
use Inertia\Inertia;
use Inertia\Response;
use Shop\Models\Product;

final class ArchivedProductController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Product::class);

        return Inertia::render('admin/products/index', [
            'view' => 'archived',
            'products' => ProductResource::collection(Product::onlyTrashed()->with(['prices', 'media'])->ordered()->get()),
        ]);
    }
}
