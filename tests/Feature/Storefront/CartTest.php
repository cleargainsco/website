<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

test('anyone can add to the cart and see it, priced at the tier the quantity reaches', function () {
    $product = researchPeptide('10 mg')->priced([1 => 5000, 3 => 4500])->create(['name' => 'Alpha', 'stock' => 20]);

    $this->post(route('cart.items.store'), ['product' => $product->public_id, 'quantity' => 3])
        ->assertSessionHasNoErrors()
        ->assertInertiaFlash('cartAdded.product_id', $product->public_id)
        ->assertInertiaFlash('cartAdded.lines.0.quantity', 3)
        ->assertInertiaFlash('cartAdded.subtotal', ['minor' => 13500, 'currency' => 'USD']);

    $this->get(route('cart.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/cart')
            ->where('cartCount', 3)
            ->has('lines', 1)
            ->where('lines.0.name', 'Alpha')
            ->where('lines.0.unit_price', ['minor' => 4500, 'currency' => 'USD'])
            ->where('subtotal', ['minor' => 13500, 'currency' => 'USD'])
            ->where('shipping', null));
});

test('the cart refuses more than is in stock, with a message', function () {
    $product = researchPeptide()->priced()->create(['name' => 'Alpha', 'stock' => 2]);

    $this->post(route('cart.items.store'), ['product' => $product->public_id, 'quantity' => 3])
        ->assertSessionHasErrors(['quantity' => 'Only 2 of Alpha left.']);
});

test('quantities can be changed and lines removed', function () {
    $product = researchPeptide()->priced()->create(['stock' => 20]);
    $this->post(route('cart.items.store'), ['product' => $product->public_id, 'quantity' => 1]);

    $this->patch(route('cart.items.update', $product), ['quantity' => 6]);
    $this->get(route('cart.show'))->assertInertia(fn (Assert $page) => $page->where('lines.0.quantity', 6));

    $this->delete(route('cart.items.destroy', $product));
    $this->get(route('cart.show'))->assertInertia(fn (Assert $page) => $page->has('lines', 0)->where('cartCount', 0));
});

test('an unknown product is a 404', function () {
    $this->post(route('cart.items.store'), ['product' => '01JBZZZZZZZZZZZZZZZZZZZZZZ', 'quantity' => 1])->assertNotFound();
});
