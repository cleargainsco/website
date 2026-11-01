<?php

declare(strict_types=1);

use App\Enums\Permission;
use App\Models\User;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\PermissionRegistrar;

test('admins pass a permission that has not been seeded yet, and customers do not', function () {
    PermissionModel::findByName(Permission::MarketingManage->value)->delete();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect(User::factory()->admin()->create()->can(Permission::MarketingManage->value))->toBeTrue()
        ->and(User::factory()->customer()->create()->can(Permission::MarketingManage->value))->toBeFalse();
});
