<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::UsersView->value);
    }

    public function view(User $user, SpatieRole $role): bool
    {
        return $user->hasPermissionTo(Permission::UsersView->value);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::SuperAdmin->value);
    }

    public function update(User $user, SpatieRole $role): bool
    {
        return $user->hasRole(Role::SuperAdmin->value);
    }

    public function delete(User $user, SpatieRole $role): bool
    {
        if ($role->name === Role::SuperAdmin->value) {
            return false;
        }

        return $user->hasRole(Role::SuperAdmin->value);
    }
}
