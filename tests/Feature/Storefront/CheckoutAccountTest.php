<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->post(route('cart.items.store'), ['product' => researchPeptide()->priced([1 => 5000])->create(['stock' => 5])->public_id, 'quantity' => 1]);
});

test('a guest signs in or registers on the checkout page itself, with their order beside it', function () {
    $this->get(route('checkout.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/checkout')
            ->where('step', 'account')
            ->has('lines', 1)
            ->has('passwordRules'));
});

test('logging in from checkout comes back to checkout, ready to order', function () {
    $customer = User::factory()->customer()->create();

    $this->get(route('checkout.create'));

    $this->post(route('login.store'), ['email' => $customer->email, 'password' => 'password'])
        ->assertRedirect(route('checkout.create'));

    $this->get(route('checkout.create'))
        ->assertInertia(fn (Assert $page) => $page->where('step', 'details')->has('address'));
});

test('registering from checkout comes back to checkout to confirm the email', function () {
    $this->get(route('checkout.create'));

    $this->post(route('register.store'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('checkout.create'));

    $this->get(route('checkout.create'))
        ->assertInertia(fn (Assert $page) => $page->where('step', 'verify'));
});

test('the verification link returns to checkout', function () {
    $customer = User::factory()->unverified()->customer()->create();

    $this->actingAs($customer)->get(route('checkout.create'));

    $link = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
        'id' => $customer->id,
        'hash' => sha1($customer->email),
    ]);

    $this->get($link)->assertRedirect(route('checkout.create'));

    expect($customer->refresh()->hasVerifiedEmail())->toBeTrue();
});
