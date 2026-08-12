<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\LaboratoryTest;
use App\Models\User;

class LaboratoryTestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::LaboratoryTestsView->value,
            Permission::LaboratoryTestsCreate->value,
            Permission::LaboratoryTestsUpdate->value,
            Permission::LaboratoryTestsDelete->value,
        ]);
    }

    public function view(User $user, LaboratoryTest $test): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::LaboratoryTestsCreate->value);
    }

    public function update(User $user, LaboratoryTest $test): bool
    {
        return $user->hasPermissionTo(Permission::LaboratoryTestsUpdate->value);
    }

    public function delete(User $user, LaboratoryTest $test): bool
    {
        return $user->hasPermissionTo(Permission::LaboratoryTestsDelete->value);
    }

    public function deleteAny(User $user): bool
    {
        return $this->delete($user, app(LaboratoryTest::class));
    }

    public function restore(User $user, LaboratoryTest $test): bool
    {
        return $this->delete($user, $test);
    }

    public function forceDelete(User $user, LaboratoryTest $test): bool
    {
        return $this->delete($user, $test);
    }
}
