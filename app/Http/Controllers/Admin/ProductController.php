<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\Admin\ProductBatchResource;
use App\Http\Resources\Admin\ProductFormResource;
use App\Http\Resources\Admin\ProductResource;
use App\Models\ProductBatch;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Shop\Actions\CreateProductAction;
use Shop\Actions\RemoveProductAction;
use Shop\Actions\UpdateProductAction;
use Shop\Contracts\ProductType;
use Shop\Enums\ProductRemoval;
use Shop\Models\Product;
use Shop\ProductTypeRegistry;
use Shop\ProductTypes\ProductField;

final class ProductController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Product::class);

        return $this->productsPage();
    }

    public function create(ProductTypeRegistry $types): Response
    {
        $this->authorize('create', Product::class);

        return $this->productsPage([
            'sheet' => 'create',
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

        return $this->productsPage([
            'sheet' => 'edit',
            'product' => $product->load(['prices', 'media'])->toResource(ProductFormResource::class),
            'batches' => ProductBatchResource::collection(
                ProductBatch::query()->whereBelongsTo($product)->with('media')->latest('tested_at')->latest('id')->get(),
            ),
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

    public function destroy(Product $product, RemoveProductAction $removeProduct): RedirectResponse
    {
        $this->authorize('delete', $product);

        $message = match ($removeProduct->handle($product)) {
            ProductRemoval::Deleted => __(':name deleted.', ['name' => $product->name]),
            ProductRemoval::Archived => __(':name archived. Its orders keep their history.', ['name' => $product->name]),
        };

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('admin.products.index');
    }

    /**
     * The add and edit forms open in a sheet over the product list, so each keeps its
     * own address for refresh, back and validation errors.
     *
     * @param  array<string, mixed>  $sheet
     */
    private function productsPage(array $sheet = []): Response
    {
        return Inertia::render('admin/products/index', [
            'view' => 'active',
            'products' => ProductResource::collection(Product::query()->with(['prices', 'media'])->ordered()->get()),
            ...$sheet,
        ]);
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
