<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::AppointmentsView->value,
            Permission::AppointmentsManage->value,
        ]);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->isAdmin() || $this->isSecretary($user)) {
            return true;
        }

        if ($this->isDoctor($user)) {
            return $appointment->doctor_id === $this->doctorIdFor($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::AppointmentsCreate->value,
            Permission::AppointmentsManage->value,
        ]);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if (! $user->hasAnyPermission([Permission::AppointmentsUpdate->value, Permission::AppointmentsManage->value])) {
            return false;
        }

        if ($user->isAdmin() || $this->isSecretary($user)) {
            return true;
        }

        return $this->isDoctor($user) && $appointment->doctor_id === $this->doctorIdFor($user);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyPermission([
            Permission::AppointmentsDelete->value,
            Permission::AppointmentsManage->value,
        ]);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::AppointmentsDelete->value,
            Permission::AppointmentsManage->value,
        ]);
    }

    public function deleteAll(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::AppointmentsDelete->value,
            Permission::AppointmentsManage->value,
        ]);
    }

    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyPermission([
            Permission::AppointmentsConfirm->value,
            Permission::AppointmentsManage->value,
        ]);
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyPermission([
            Permission::AppointmentsCancel->value,
            Permission::AppointmentsManage->value,
        ]);
    }

    public function calendar(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::AppointmentsCalendar->value,
            Permission::AppointmentsManage->value,
        ]);
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyPermission([
            Permission::AppointmentsDelete->value,
            Permission::AppointmentsManage->value,
        ]);
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return $this->delete($user, $appointment);
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
