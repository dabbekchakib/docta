<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\ChronicDisease;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class ChronicDiseasePolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ChronicDiseasesManage->value);
    }

    public function view(User $user, ChronicDisease $disease): bool
    {
        return $this->canManageFor($user, $disease);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ChronicDiseasesManage->value);
    }

    public function update(User $user, ChronicDisease $disease): bool
    {
        return $this->canManageFor($user, $disease);
    }

    public function delete(User $user, ChronicDisease $disease): bool
    {
        return $this->canManageFor($user, $disease);
    }

    public function deleteAny(User $user): bool
    {
        return $this->create($user);
    }

    private function canManageFor(User $user, ChronicDisease $disease): bool
    {
        if (! $user->hasPermissionTo(Permission::ChronicDiseasesManage->value)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $this->isDoctorOfPatient($user, $this->medicalRecordOf($disease));
    }
}
