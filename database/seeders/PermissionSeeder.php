<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\RoleName;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        collect(Permission::values())->each(fn (string $name): PermissionModel => PermissionModel::query()->firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        PermissionModel::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', Permission::values())
            ->get()
            ->each(function (PermissionModel $permission): void {
                $permission->roles()->detach();
                $permission->users()->detach();
                $permission->delete();
            });

        collect(RoleName::cases())->each(fn (RoleName $roleName): Role => Role::findByName($roleName->value, 'web')
            ->syncPermissions(array_map(fn (Permission $permission): string => $permission->value, $roleName->permissions())));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
