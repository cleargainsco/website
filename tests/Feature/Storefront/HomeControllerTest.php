<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

it('renders only featured products', function () {
    config(['storefront.products' => [
        ['slug' => 'alpha', 'name' => 'Alpha', 'amount' => '10 mg', 'summary' => '', 'stock' => 'in_stock', 'featured' => true, 'prices' => [1 => 100], 'batches' => []],
        ['slug' => 'beta', 'name' => 'Beta', 'amount' => '5 mg', 'summary' => '', 'stock' => 'in_stock', 'featured' => false, 'prices' => [1 => 100], 'batches' => []],
    ]]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/home')
            ->has('products', 1)
            ->where('products.0.slug', 'alpha'));
});
