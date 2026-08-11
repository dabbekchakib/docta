<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Medication;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class MedicationPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::MedicationsManage->value);
    }

    public function view(User $user, Medication $medication): bool
    {
        return $this->canManageFor($user, $medication);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::MedicationsManage->value);
    }

    public function update(User $user, Medication $medication): bool
    {
        return $this->canManageFor($user, $medication);
    }

    public function delete(User $user, Medication $medication): bool
    {
        return $this->canManageFor($user, $medication);
    }

    public function deleteAny(User $user): bool
    {
        return $this->create($user);
    }

    private function canManageFor(User $user, Medication $medication): bool
    {
        if (! $user->hasPermissionTo(Permission::MedicationsManage->value)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $this->isDoctorOfPatient($user, $this->medicalRecordOf($medication));
    }
}
