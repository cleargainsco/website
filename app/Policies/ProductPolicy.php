<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Shop\Models\Product;

final class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ProductsManage->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::ProductsManage->value);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can(Permission::ProductsManage->value);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can(Permission::ProductsManage->value);
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->can(Permission::ProductsManage->value);
    }
}
