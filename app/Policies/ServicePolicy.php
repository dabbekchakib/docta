<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ServicesView->value);
    }

    public function view(User $user, Service $service): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ServicesCreate->value);
    }

    public function update(User $user, Service $service): bool
    {
        return $user->hasPermissionTo(Permission::ServicesUpdate->value);
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->hasPermissionTo(Permission::ServicesDelete->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ServicesDelete->value);
    }

    public function restore(User $user, Service $service): bool
    {
        return $user->hasPermissionTo(Permission::ServicesDelete->value);
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $user->hasPermissionTo(Permission::ServicesDelete->value);
    }
}
