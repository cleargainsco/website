<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Shop\Models\Order;

final class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::OrdersManage->value);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can(Permission::OrdersManage->value);
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can(Permission::OrdersManage->value);
    }
}
