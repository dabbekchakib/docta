<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class MedicalRecordPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::MedicalRecordsView->value,
            Permission::MedicalRecordsUpdate->value,
        ]);
    }

    public function view(User $user, MedicalRecord $record): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $this->isDoctorOfPatient($user, $record);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::MedicalRecordsCreate->value,
            Permission::MedicalRecordsUpdate->value,
        ]);
    }

    public function update(User $user, MedicalRecord $record): bool
    {
        if (! $user->hasAnyPermission([Permission::MedicalRecordsUpdate->value, Permission::MedicalRecordsCreate->value])) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $this->isDoctorOfPatient($user, $record);
    }

    public function delete(User $user, MedicalRecord $record): bool
    {
        return $user->hasPermissionTo(Permission::MedicalRecordsDelete->value) && $user->isAdmin();
    }

    public function export(User $user, MedicalRecord $record): bool
    {
        return $this->view($user, $record);
    }
}
