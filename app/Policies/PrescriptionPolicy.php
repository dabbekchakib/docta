<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\PrescriptionStatus;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class PrescriptionPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::PrescriptionsView->value,
            Permission::PrescriptionsCreate->value,
            Permission::PrescriptionsUpdate->value,
            Permission::PrescriptionsIssue->value,
            Permission::PrescriptionsPrint->value,
        ]);
    }

    public function view(User $user, Prescription $prescription): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->isAdmin() || $this->isSecretary($user)) {
            return true;
        }

        if ($this->isDoctor($user)) {
            return $prescription->doctor_id === $this->doctorIdFor($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PrescriptionsCreate->value);
    }

    public function update(User $user, Prescription $prescription): bool
    {
        if (! $user->hasPermissionTo(Permission::PrescriptionsUpdate->value)) {
            return false;
        }

        if (! $prescription->isEditable()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $prescription->doctor_id === $this->doctorIdFor($user);
    }

    public function delete(User $user, Prescription $prescription): bool
    {
        return $user->hasPermissionTo(Permission::PrescriptionsDelete->value);
    }

    public function deleteAny(User $user): bool
    {
        return $this->delete($user, app(Prescription::class));
    }

    public function issue(User $user, Prescription $prescription): bool
    {
        if (! $user->hasPermissionTo(Permission::PrescriptionsIssue->value)) {
            return false;
        }

        if (! $prescription->isEditable()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $prescription->doctor_id === $this->doctorIdFor($user);
    }

    public function cancel(User $user, Prescription $prescription): bool
    {
        if (! $user->hasPermissionTo(Permission::PrescriptionsCancel->value)) {
            return false;
        }

        if (in_array($prescription->status, [PrescriptionStatus::Cancelled, PrescriptionStatus::Expired], true)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $prescription->doctor_id === $this->doctorIdFor($user);
    }

    public function print(User $user, Prescription $prescription): bool
    {
        if (! $user->hasPermissionTo(Permission::PrescriptionsPrint->value)) {
            return false;
        }

        if ($user->isAdmin() || $this->isSecretary($user)) {
            return true;
        }

        return $this->isDoctor($user) && $prescription->doctor_id === $this->doctorIdFor($user);
    }

    public function export(User $user, Prescription $prescription): bool
    {
        if (! $user->hasPermissionTo(Permission::PrescriptionsExport->value)) {
            return false;
        }

        return $this->view($user, $prescription);
    }

    public function restore(User $user, Prescription $prescription): bool
    {
        return $this->delete($user, $prescription);
    }

    public function forceDelete(User $user, Prescription $prescription): bool
    {
        return $this->delete($user, $prescription);
    }

    private function doctorIdFor(User $user): ?int
    {
        return Doctor::query()->where('user_id', $user->id)->value('id');
    }
}
