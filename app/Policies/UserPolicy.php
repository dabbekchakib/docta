<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::UsersView->value);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo(Permission::UsersView->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::UsersCreate->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo(Permission::UsersUpdate->value);
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->is($user)) {
            return false;
        }

        return $user->hasPermissionTo(Permission::UsersDelete->value);
    }
}
