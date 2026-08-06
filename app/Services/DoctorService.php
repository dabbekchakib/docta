<?php

namespace App\Services;

use App\Enums\DoctorStatus;
use App\Enums\Role;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role as RoleModel;

class DoctorService
{
    /**
     * Génère le code médecin unique (DOC-000001).
     */
    public function generateDoctorCode(): string
    {
        $sequence = Doctor::withTrashed()->max('id') ?? 0;

        return 'DOC-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crée un médecin et garantit le rôle « doctor » sur le compte utilisateur.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Doctor
    {
        $doctor = Doctor::create($attributes);

        $this->ensureDoctorRole($doctor);

        return $doctor;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Doctor $doctor, array $attributes): Doctor
    {
        $doctor->update($attributes);

        return $doctor;
    }

    public function deactivate(Doctor $doctor): Doctor
    {
        return $this->changeStatus($doctor, DoctorStatus::Inactive, 'Médecin désactivé');
    }

    public function reactivate(Doctor $doctor): Doctor
    {
        return $this->changeStatus($doctor, DoctorStatus::Active, 'Médecin réactivé');
    }

    /**
     * Affecte automatiquement le rôle « doctor » au compte utilisateur lié.
     */
    public function ensureDoctorRole(Doctor $doctor): void
    {
        $user = $doctor->user;

        if (! $user || $user->hasRole(Role::Doctor->value)) {
            return;
        }

        RoleModel::findOrCreate(Role::Doctor->value);

        $user->assignRole(Role::Doctor->value);
    }

    private function changeStatus(Doctor $doctor, DoctorStatus $status, string $description): Doctor
    {
        $doctor->update(['status' => $status]);

        activity('doctors')
            ->performedOn($doctor)
            ->causedBy(Auth::user())
            ->log($description);

        return $doctor;
    }
}
