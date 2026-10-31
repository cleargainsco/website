<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\Admin\ProductFormResource;
use App\Http\Resources\Admin\ProductResource;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Shop\Actions\CreateProductAction;
use Shop\Actions\UpdateProductAction;
use Shop\Contracts\ProductType;
use Shop\Models\Product;
use Shop\ProductTypeRegistry;
use Shop\ProductTypes\ProductField;

final class ProductController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Product::class);

        return Inertia::render('admin/products/index', [
            'products' => ProductResource::collection(Product::query()->with('prices')->ordered()->get()),
        ]);
    }

    public function create(ProductTypeRegistry $types): Response
    {
        $this->authorize('create', Product::class);

        return Inertia::render('admin/products/create', [
            'productTypes' => $this->productTypes($types),
            'currency' => config()->string('shop.currency'),
        ]);
    }

    public function store(StoreProductRequest $request, CreateProductAction $createProduct): RedirectResponse
    {
        $product = $createProduct->handle($request->toProductData());

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name created.', ['name' => $product->name])]);

        return to_route('admin.products.index');
    }

    public function edit(Product $product, ProductTypeRegistry $types): Response
    {
        $this->authorize('update', $product);

        return Inertia::render('admin/products/edit', [
            'product' => $product->load('prices')->toResource(ProductFormResource::class),
            'productTypes' => $this->productTypes($types),
            'currency' => config()->string('shop.currency'),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $updateProduct): RedirectResponse
    {
        $updateProduct->handle($product, $request->toProductData());

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name saved.', ['name' => $product->name])]);

        return to_route('admin.products.index');
    }

    /**
     * @return list<array{key: string, label: string, fields: list<ProductField>}>
     */
    private function productTypes(ProductTypeRegistry $types): array
    {
        return array_values(collect($types->all())
            ->map(fn (ProductType $type, string $key): array => ['key' => $key, 'label' => $type->label(), 'fields' => $type->fields()])
            ->all());
    }
}
