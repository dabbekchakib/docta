<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Doctor;
use App\Models\User;

class DoctorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DoctorsView->value);
    }

    public function view(User $user, Doctor $doctor): bool
    {
        return $user->hasPermissionTo(Permission::DoctorsView->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DoctorsCreate->value);
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return $user->hasPermissionTo(Permission::DoctorsUpdate->value);
    }

    public function delete(User $user, Doctor $doctor): bool
    {
        return $user->hasPermissionTo(Permission::DoctorsDelete->value);
    }

    public function restore(User $user, Doctor $doctor): bool
    {
        return $user->hasPermissionTo(Permission::DoctorsDelete->value);
    }

    public function forceDelete(User $user, Doctor $doctor): bool
    {
        return $user->hasPermissionTo(Permission::DoctorsDelete->value);
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DoctorsExport->value);
    }
}
