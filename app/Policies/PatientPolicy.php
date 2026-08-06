<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PatientsView->value);
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo(Permission::PatientsView->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PatientsCreate->value);
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo(Permission::PatientsUpdate->value);
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo(Permission::PatientsDelete->value);
    }

    public function restore(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo(Permission::PatientsDelete->value);
    }

    public function forceDelete(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo(Permission::PatientsDelete->value);
    }
}
