<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shop\Models\Product;

beforeEach(function () {
    config([
        'storefront.storage' => 'Keep frozen.',
        'storefront.batches' => [
            'alpha-10mg' => [
                ['number' => 'A-002', 'tested_at' => '2026-10-01', 'purity' => '99.1%', 'lab' => 'Lab One', 'report' => 'alpha-a-002.pdf'],
            ],
        ],
    ]);

    researchPeptide('10 mg')->featured()->priced([1 => 5000, 3 => 4500])
        ->create(['slug' => 'alpha-10mg', 'name' => 'Alpha', 'sort_order' => 1, 'stock' => 40]);

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
    it('renders the product with its batch report and the other products as related', function () {
        $this->get(route('shop.show', 'alpha-10mg'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('marketing/shop/show')
                ->where('product.slug', 'alpha-10mg')
                ->where('product.batches.0.report_url', asset('coas/alpha-a-002.pdf'))
                ->has('related', 1)
                ->where('related.0.slug', 'beta-5mg'));
    });

    it('returns 404 for an unknown or hidden product', function () {
        researchPeptide()->inactive()->priced()->create(['slug' => 'hidden']);

        $this->get(route('shop.show', 'gamma'))->assertNotFound();
        $this->get(route('shop.show', 'hidden'))->assertNotFound();
    });
});
