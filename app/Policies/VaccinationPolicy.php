<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Models\Vaccination;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class VaccinationPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::VaccinationsManage->value);
    }

    public function view(User $user, Vaccination $vaccination): bool
    {
        return $this->canManageFor($user, $vaccination);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::VaccinationsManage->value);
    }

    public function update(User $user, Vaccination $vaccination): bool
    {
        return $this->canManageFor($user, $vaccination);
    }

    public function delete(User $user, Vaccination $vaccination): bool
    {
        return $this->canManageFor($user, $vaccination);
    }

    public function deleteAny(User $user): bool
    {
        return $this->create($user);
    }

    private function canManageFor(User $user, Vaccination $vaccination): bool
    {
        if (! $user->hasPermissionTo(Permission::VaccinationsManage->value)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $this->isDoctorOfPatient($user, $this->medicalRecordOf($vaccination));
    }
}
