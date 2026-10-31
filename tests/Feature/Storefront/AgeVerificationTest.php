<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

test('visitors are asked their age until they confirm it', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('ageVerified', false));

    $this->from(route('about'))
        ->post(route('age-verification.store'))
        ->assertRedirect(route('about'))
        ->assertCookie('age_verified', '1');
});

test('a visitor who confirmed their age is not asked again', function () {
    $this->withCookie('age_verified', '1')
        ->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('ageVerified', true));
});

test('the gate does not hide the page from search engines', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('<title data-inertia="title">About | Clear Gains</title>', false);
});
