<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

it('renders only featured products', function () {
    researchPeptide()->featured()->priced()->create(['slug' => 'alpha']);
    researchPeptide()->priced()->create(['slug' => 'beta']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/home')
            ->has('products', 1)
            ->where('products.0.slug', 'alpha'));
});
