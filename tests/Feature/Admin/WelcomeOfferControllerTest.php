<?php

declare(strict_types=1);

use App\Models\Subscriber;
use App\Models\User;
use App\Models\WelcomeOffer;
use Inertia\Testing\AssertableInertia as Assert;

function welcomeOfferForm(array $overrides = []): array
{
    return array_replace([
        'percent_off' => 15,
        'valid_days' => 14,
        'eyebrow' => 'New here?',
        'headline' => 'Take 15% off',
        'body' => 'Join the list.',
        'button_label' => 'Send my code',
        'success_headline' => 'Sent',
        'success_body' => 'Check your inbox.',
        'fine_print' => 'One code per address.',
    ], $overrides);
}

test('admins see the offer with its default copy', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.welcome-offer.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/welcome-offer/edit')
            ->where('offer.is_enabled', false)
            ->where('offer.percent_off', 10)
            ->where('offer.headline', 'Take 10% off your first order')
            ->where('stats.codes_sent', 0));
});

test('an admin rewrites the offer without changing whether it is live', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.welcome-offer.update'), welcomeOfferForm(['valid_days' => '']))
        ->assertSessionHasNoErrors();

    $offer = WelcomeOffer::current();

    expect($offer->isEnabled())->toBeFalse()
        ->and($offer->percent_off)->toBe(15)
        ->and($offer->valid_days)->toBeNull()
        ->and($offer->headline)->toBe('Take 15% off');
});

test('the switch turns the offer on and off on its own', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->put(route('admin.welcome-offer.status.update'), ['is_enabled' => '1'])->assertSessionHasNoErrors();
    expect(WelcomeOffer::current()->isEnabled())->toBeTrue();

    $this->actingAs($admin)->put(route('admin.welcome-offer.status.update'), ['is_enabled' => '0']);
    expect(WelcomeOffer::current()->isEnabled())->toBeFalse();
});

test('the discount has to be a sensible percentage', function (int $percent) {
    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.welcome-offer.update'), welcomeOfferForm(['percent_off' => $percent]))
        ->assertSessionHasErrors('percent_off');
})->with([0, 91]);

test('customers cannot see or change the offer, and learn nothing about the form', function () {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)->get(route('admin.welcome-offer.edit'))->assertForbidden();
    $this->actingAs($customer)->put(route('admin.welcome-offer.update'), [])
        ->assertForbidden()
        ->assertSessionDoesntHaveErrors();
    $this->actingAs($customer)->put(route('admin.welcome-offer.status.update'), [])
        ->assertForbidden()
        ->assertSessionDoesntHaveErrors();
});

test('admins see who has signed up and whether they reached Resend', function () {
    Subscriber::factory()->synced()->create(['email' => 'ada@example.com']);
    Subscriber::factory()->withoutConsent()->create();
    config(['services.resend.segments.general' => 'seg_1']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.subscribers.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/subscribers/index')
            ->has('subscribers.data', 2)
            ->where('totals', ['all' => 2, 'consented' => 1, 'synced' => 1])
            ->where('resend_configured', true));
});

test('customers cannot see the subscriber list', function () {
    $this->actingAs(User::factory()->customer()->create())
        ->get(route('admin.subscribers.index'))
        ->assertForbidden();
});
