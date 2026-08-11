<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Allergy;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class AllergyPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::AllergiesManage->value);
    }

    public function view(User $user, Allergy $allergy): bool
    {
        return $this->canManageFor($user, $allergy);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::AllergiesManage->value);
    }

    public function update(User $user, Allergy $allergy): bool
    {
        return $this->canManageFor($user, $allergy);
    }

    public function delete(User $user, Allergy $allergy): bool
    {
        return $this->canManageFor($user, $allergy);
    }

    public function deleteAny(User $user): bool
    {
        return $this->create($user);
    }

    private function canManageFor(User $user, Allergy $allergy): bool
    {
        if (! $user->hasPermissionTo(Permission::AllergiesManage->value)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $this->isDoctorOfPatient($user, $this->medicalRecordOf($allergy));
    }
}
