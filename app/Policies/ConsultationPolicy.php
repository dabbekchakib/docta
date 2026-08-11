<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\User;

class ConsultationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::ConsultationsView->value,
            Permission::ConsultationsManage->value,
        ]);
    }

    public function view(User $user, Consultation $consultation): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->isAdmin() || $this->isSecretary($user)) {
            return true;
        }

        if ($this->isDoctor($user)) {
            return $consultation->doctor_id === $this->doctorIdFor($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::ConsultationsCreate->value,
            Permission::ConsultationsManage->value,
        ]);
    }

    public function update(User $user, Consultation $consultation): bool
    {
        if (! $user->hasAnyPermission([Permission::ConsultationsUpdate->value, Permission::ConsultationsManage->value])) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $consultation->doctor_id === $this->doctorIdFor($user);
    }

    public function delete(User $user, Consultation $consultation): bool
    {
        return $user->hasAnyPermission([
            Permission::ConsultationsDelete->value,
            Permission::ConsultationsManage->value,
        ]);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::ConsultationsDelete->value,
            Permission::ConsultationsManage->value,
        ]);
    }

    public function deleteAll(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::ConsultationsDelete->value,
            Permission::ConsultationsManage->value,
        ]);
    }

    public function print(User $user, Consultation $consultation): bool
    {
        if (! $user->hasAnyPermission([Permission::ConsultationsPrint->value, Permission::ConsultationsManage->value])) {
            return false;
        }

        if ($user->isAdmin() || $this->isSecretary($user)) {
            return true;
        }

        return $this->isDoctor($user) && $consultation->doctor_id === $this->doctorIdFor($user);
    }

    public function restore(User $user, Consultation $consultation): bool
    {
        return $user->hasAnyPermission([
            Permission::ConsultationsDelete->value,
            Permission::ConsultationsManage->value,
        ]);
    }

    public function forceDelete(User $user, Consultation $consultation): bool
    {
        return $this->delete($user, $consultation);
    }

    private function isDoctor(User $user): bool
    {
        return $user->hasRole('doctor');
    }

    private function doctorIdFor(User $user): ?int
    {
        return Doctor::query()->where('user_id', $user->id)->value('id');
    }

    private function isSecretary(User $user): bool
    {
        return $user->hasRole('secretary');
    }
}
