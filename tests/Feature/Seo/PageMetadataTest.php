<?php

declare(strict_types=1);

use App\Enums\SeoPage;
use App\Models\SeoPageSetting;
use App\Models\User;

describe('public pages', function () {
    test('a page renders its default title and description into the html', function () {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('<title data-inertia="title">About | Clear Gains</title>', false)
            ->assertSee('content="'.SeoPage::About->defaultDescription().'"', false)
            ->assertSee('content="all"', false);
    });

    test('what an admin saved replaces the default, field by field', function () {
        SeoPageSetting::query()->create(['page' => SeoPage::About, 'title' => 'Who we are']);

        $this->get(route('about'))
            ->assertSee('<title data-inertia="title">Who we are | Clear Gains</title>', false)
            ->assertSee('content="'.SeoPage::About->defaultDescription().'"', false);
    });
});

describe('private pages', function () {
    test('the admin, account and sign-in pages ask search engines not to index them', function (string $path, Closure $user) {
        $signedIn = $user();

        if ($signedIn !== null) {
            $this->actingAs($signedIn);
        }

        $this->get($path)->assertOk()->assertSee('name="robots" content="noindex, nofollow"', false);
    })->with([
        'dashboard' => ['/dashboard', fn () => User::factory()->admin()->create()],
        'account' => ['/account', fn () => User::factory()->customer()->create()],
        'login' => ['/login', fn () => null],
    ]);
});
