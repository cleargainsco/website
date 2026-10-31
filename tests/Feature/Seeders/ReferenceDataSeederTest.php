<?php

use App\Enums\Permission;
use App\Enums\RoleName;
use Database\Seeders\ReferenceDataSeeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;

test('it syncs roles and permissions to the enums and is safe to re-run', function () {
    Role::query()->create(['name' => 'Retired Role', 'guard_name' => 'web']);
    PermissionModel::query()->create(['name' => 'retired.permission', 'guard_name' => 'web']);

    $this->seed(ReferenceDataSeeder::class);
    $this->seed(ReferenceDataSeeder::class);

    expect(Role::query()->orderBy('name')->pluck('name')->all())->toBe(RoleName::values())
        ->and(PermissionModel::query()->pluck('name')->sort()->values()->all())->toBe(collect(Permission::values())->sort()->values()->all())
        ->and(Role::findByName(RoleName::Admin->value)->hasPermissionTo(Permission::AdminAccess->value))->toBeTrue()
        ->and(Role::findByName(RoleName::Customer->value)->permissions)->toBeEmpty();
});
