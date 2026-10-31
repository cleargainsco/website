<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config([
        'storefront.currency' => 'USD',
        'storefront.storage' => 'Keep frozen.',
        'storefront.products' => [
            [
                'slug' => 'alpha-10mg',
                'name' => 'Alpha',
                'amount' => '10 mg',
                'summary' => 'Alpha summary.',
                'stock' => 'in_stock',
                'featured' => true,
                'prices' => [1 => 5000, 3 => 4500],
                'batches' => [
                    ['number' => 'A-002', 'tested_at' => '2026-10-01', 'purity' => '99.1%', 'lab' => 'Lab One', 'report' => 'alpha-a-002.pdf'],
                ],
            ],
            [
                'slug' => 'beta-5mg',
                'name' => 'Beta',
                'amount' => '5 mg',
                'summary' => 'Beta summary.',
                'stock' => 'out_of_stock',
                'featured' => false,
                'storage' => 'Keep refrigerated.',
                'prices' => [1 => 3000],
                'batches' => [],
            ],
        ],
    ]);
});

describe('index', function () {
    it('renders every product with per-tier totals', function () {
        $this->get(route('shop.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('marketing/shop/index')
                ->has('products', 2)
                ->where('products.0.price', ['minor' => 5000, 'currency' => 'USD'])
                ->where('products.0.tiers.1', [
                    'quantity' => 3,
                    'unit' => ['minor' => 4500, 'currency' => 'USD'],
                    'total' => ['minor' => 13500, 'currency' => 'USD'],
                ])
                ->where('products.1.stock', 'out_of_stock'));
    });

    it('falls back to the default storage guidance unless the product sets its own', function () {
        $this->get(route('shop.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.0.storage', 'Keep frozen.')
                ->where('products.1.storage', 'Keep refrigerated.'));
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

    it('returns 404 for an unknown product', function () {
        $this->get(route('shop.show', 'gamma'))->assertNotFound();
    });
});
