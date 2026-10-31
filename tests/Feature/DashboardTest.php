<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('admins can visit the dashboard', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('dashboard'))->assertOk();
});

test('customers cannot visit the dashboard', function () {
    $this->actingAs(User::factory()->customer()->create());

    $this->get(route('dashboard'))->assertForbidden();
});
