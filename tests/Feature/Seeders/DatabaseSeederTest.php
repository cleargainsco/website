<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\User;
use Database\Factories\UserFactory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

test('it seeds the fixture accounts on staging', function () {
    app()->detectEnvironment(fn (): string => 'staging');

    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@example.com')->sole();

    expect(Hash::check(UserFactory::PASSWORD, $admin->password))->toBeTrue()
        ->and($admin->hasRole(RoleName::Admin))->toBeTrue();
});

test('it refuses to run in production', function () {
    app()->detectEnvironment(fn (): string => 'production');

    expect(fn () => app(DatabaseSeeder::class)->run())->toThrow(RuntimeException::class)
        ->and(User::query()->count())->toBe(0);
});
