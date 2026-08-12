<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Laboratory;
use App\Models\User;

class LaboratoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::LaboratoriesView->value,
            Permission::LaboratoriesCreate->value,
            Permission::LaboratoriesUpdate->value,
            Permission::LaboratoriesDelete->value,
        ]);
    }

    public function view(User $user, Laboratory $laboratory): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::LaboratoriesCreate->value);
    }

    public function update(User $user, Laboratory $laboratory): bool
    {
        return $user->hasPermissionTo(Permission::LaboratoriesUpdate->value);
    }

    public function delete(User $user, Laboratory $laboratory): bool
    {
        return $user->hasPermissionTo(Permission::LaboratoriesDelete->value);
    }

    public function deleteAny(User $user): bool
    {
        return $this->delete($user, app(Laboratory::class));
    }

    public function restore(User $user, Laboratory $laboratory): bool
    {
        return $this->delete($user, $laboratory);
    }

    public function forceDelete(User $user, Laboratory $laboratory): bool
    {
        return $this->delete($user, $laboratory);
    }
}
