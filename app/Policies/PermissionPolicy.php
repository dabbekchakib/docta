<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission as SpatiePermission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::UsersView->value);
    }

    public function view(User $user, SpatiePermission $permission): bool
    {
        return $user->hasPermissionTo(Permission::UsersView->value);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::SuperAdmin->value);
    }

    public function update(User $user, SpatiePermission $permission): bool
    {
        return $user->hasRole(Role::SuperAdmin->value);
    }

    public function delete(User $user, SpatiePermission $permission): bool
    {
        return $user->hasRole(Role::SuperAdmin->value);
    }
}
