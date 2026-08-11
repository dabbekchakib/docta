<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\MedicalHistory;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class MedicalHistoryPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::MedicalHistoriesManage->value);
    }

    public function view(User $user, MedicalHistory $history): bool
    {
        return $this->canManageFor($user, $history);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::MedicalHistoriesManage->value);
    }

    public function update(User $user, MedicalHistory $history): bool
    {
        return $this->canManageFor($user, $history);
    }

    public function delete(User $user, MedicalHistory $history): bool
    {
        return $this->canManageFor($user, $history);
    }

    public function deleteAny(User $user): bool
    {
        return $this->create($user);
    }

    private function canManageFor(User $user, MedicalHistory $history): bool
    {
        if (! $user->hasPermissionTo(Permission::MedicalHistoriesManage->value)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $this->isDoctorOfPatient($user, $this->medicalRecordOf($history));
    }
}
