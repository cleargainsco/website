<?php

declare(strict_types=1);

use App\Models\ProductBatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Shop\Models\Product;

beforeEach(function () {
    Storage::fake('public');
    config(['storefront.storage' => 'Keep frozen.']);

    $alpha = researchPeptide('10 mg')->featured()->priced([1 => 5000, 3 => 4500])
        ->create(['slug' => 'alpha-10mg', 'name' => 'Alpha', 'sort_order' => 1, 'stock' => 40]);

    ProductBatch::factory()->for($alpha)->create(['number' => 'A-001', 'tested_at' => '2026-06-01'])
        ->addMedia(fakePdf('a-001.pdf'))->toMediaCollection(ProductBatch::CERTIFICATE_COLLECTION);
    ProductBatch::factory()->for($alpha)->create(['number' => 'A-002', 'tested_at' => '2026-10-01', 'purity' => '99.1%'])
        ->addMedia(fakePdf('a-002.pdf'))->toMediaCollection(ProductBatch::CERTIFICATE_COLLECTION);
    ProductBatch::factory()->for($alpha)->create(['number' => 'NO-PDF', 'tested_at' => '2026-10-20']);

    researchPeptide('5 mg')->priced([1 => 3000])
        ->create(['slug' => 'beta-5mg', 'name' => 'Beta', 'sort_order' => 2, 'stock' => 0, 'details' => ['amount' => '5 mg', 'storage' => 'Keep refrigerated.']]);
});

describe('index', function () {
    it('renders every listed product with per-tier totals and stock worked out from the count', function () {
        $this->get(route('shop.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('marketing/shop/index')
                ->has('products', 2)
                ->where('products.0.amount', '10 mg')
                ->where('products.0.price', ['minor' => 5000, 'currency' => 'USD'])
                ->where('products.0.tiers.1', [
                    'quantity' => 3,
                    'unit' => ['minor' => 4500, 'currency' => 'USD'],
                    'total' => ['minor' => 13500, 'currency' => 'USD'],
                ])
                ->where('products.0.stock', 'in_stock')
                ->where('products.1.stock', 'out_of_stock'));
    });

    it('falls back to the default storage guidance unless the product sets its own', function () {
        $this->get(route('shop.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.0.storage', 'Keep frozen.')
                ->where('products.1.storage', 'Keep refrigerated.'));
    });

    it('leaves out hidden products, unpriced products and other product types', function () {
        researchPeptide()->inactive()->priced()->create(['slug' => 'hidden']);
        researchPeptide()->create(['slug' => 'unpriced']);
        Product::factory()->priced()->create(['slug' => 'simple-thing']);

        $this->get(route('shop.index'))
            ->assertInertia(fn (Assert $page) => $page->has('products', 2));
    });
});

describe('show', function () {
    it('renders the product with its batches newest first and the other products as related', function () {
        $this->get(route('shop.show', 'alpha-10mg'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('marketing/shop/show')
                ->where('product.slug', 'alpha-10mg')
                ->has('product.batches', 2)
                ->where('product.batches.0.number', 'A-002')
                ->where('product.batches.0.tested_at', '2026-10-01')
                ->where('product.batches.0.purity', '99.1%')
                ->where('product.batches.0.report_url', fn (string $url) => str_ends_with($url, 'a-002.pdf'))
                ->where('product.batches.1.number', 'A-001')
                ->has('related', 1)
                ->where('related.0.slug', 'beta-5mg'));
    });

    it('includes the product photo when there is one', function () {
        $product = Product::query()->where('slug', 'alpha-10mg')->sole();
        $product->addMedia(UploadedFile::fake()->image('alpha.webp'))->toMediaCollection(Product::IMAGE_COLLECTION);

        $this->get(route('shop.show', 'alpha-10mg'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('product.image_url', fn (string $url) => str_ends_with($url, 'alpha.webp'))
                ->where('related.0.image_url', null));
    });

    it('returns 404 for an unknown or hidden product', function () {
        researchPeptide()->inactive()->priced()->create(['slug' => 'hidden']);

        $this->get(route('shop.show', 'gamma'))->assertNotFound();
        $this->get(route('shop.show', 'hidden'))->assertNotFound();
    });
});
