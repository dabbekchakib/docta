<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\FamilyHistory;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class FamilyHistoryPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::FamilyHistoriesManage->value);
    }

    public function view(User $user, FamilyHistory $history): bool
    {
        return $this->canManageFor($user, $history);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::FamilyHistoriesManage->value);
    }

    public function update(User $user, FamilyHistory $history): bool
    {
        return $this->canManageFor($user, $history);
    }

    public function delete(User $user, FamilyHistory $history): bool
    {
        return $this->canManageFor($user, $history);
    }

    public function deleteAny(User $user): bool
    {
        return $this->create($user);
    }

    private function canManageFor(User $user, FamilyHistory $history): bool
    {
        if (! $user->hasPermissionTo(Permission::FamilyHistoriesManage->value)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $this->isDoctorOfPatient($user, $this->medicalRecordOf($history));
    }
}
