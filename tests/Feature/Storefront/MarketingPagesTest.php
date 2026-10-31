<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

it('renders the public page to guests', function (string $route, string $component) {
    $this->get(route($route))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    'testing' => ['testing', 'marketing/testing'],
    'about' => ['about', 'marketing/about'],
    'faq' => ['faq', 'marketing/faq'],
    'shipping policy' => ['policies.shipping', 'marketing/policies/shipping'],
    'returns policy' => ['policies.returns', 'marketing/policies/returns'],
    'privacy policy' => ['policies.privacy', 'marketing/policies/privacy'],
    'terms' => ['policies.terms', 'marketing/policies/terms'],
]);

it('shares the support email with every page', function () {
    config(['storefront.support_email' => 'help@example.test']);

    $this->get(route('about'))
        ->assertInertia(fn (Assert $page) => $page->where('supportEmail', 'help@example.test'));
});
