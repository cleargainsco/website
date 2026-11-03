<?php

use App\Enums\Permission;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

dataset('account pages', [
    'overview' => ['account.index', 'account/index'],
    'orders' => ['account.orders.index', 'account/orders/index'],
    'profile' => ['account.profile.edit', 'account/profile'],
]);

test('guests are sent to log in', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with('account pages');

test('customers can open their account pages', function (string $route, string $component) {
    $this->actingAs(User::factory()->customer()->create())
        ->get(route($route))
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with('account pages');

test('customers who have not confirmed their email can still open their account pages', function (string $route, string $component) {
    $this->actingAs(User::factory()->unverified()->customer()->create())
        ->get(route($route))
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with('account pages');

test('customers cannot open the admin settings pages', function (string $route) {
    $this->actingAs(User::factory()->customer()->create())
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route($route))
        ->assertForbidden();
})->with(['profile.edit', 'security.edit']);

test('admins are sent to the dashboard instead', function (string $route) {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route($route))
        ->assertRedirect(route('dashboard'));
})->with('account pages');

test('the page shares what the user is allowed to do, without their roles', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.permissions', Permission::values())
            ->missing('auth.user.roles')
            ->missing('auth.user.permissions'),
        );

    $this->actingAs(User::factory()->customer()->create())
        ->get(route('account.index'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.permissions', []));
});
