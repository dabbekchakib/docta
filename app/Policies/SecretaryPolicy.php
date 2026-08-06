<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Secretary;
use App\Models\User;

class SecretaryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SecretariesView->value);
    }

    public function view(User $user, Secretary $secretary): bool
    {
        return $user->hasPermissionTo(Permission::SecretariesView->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SecretariesCreate->value);
    }

    public function update(User $user, Secretary $secretary): bool
    {
        return $user->hasPermissionTo(Permission::SecretariesUpdate->value);
    }

    public function delete(User $user, Secretary $secretary): bool
    {
        return $user->hasPermissionTo(Permission::SecretariesDelete->value);
    }

    public function restore(User $user, Secretary $secretary): bool
    {
        return $user->hasPermissionTo(Permission::SecretariesDelete->value);
    }

    public function forceDelete(User $user, Secretary $secretary): bool
    {
        return $user->hasPermissionTo(Permission::SecretariesDelete->value);
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SecretariesExport->value);
    }
}
