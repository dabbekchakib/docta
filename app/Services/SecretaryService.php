<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\SecretaryStatus;
use App\Models\Secretary;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role as RoleModel;

class SecretaryService
{
    /**
     * Génère le code secrétaire unique (SEC-000001).
     */
    public function generateSecretaryCode(): string
    {
        $sequence = Secretary::withTrashed()->max('id') ?? 0;

        return 'SEC-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crée une secrétaire et garantit le rôle « secretary » sur le compte utilisateur.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Secretary
    {
        $secretary = Secretary::create($attributes);

        $this->ensureSecretaryRole($secretary);

        return $secretary;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Secretary $secretary, array $attributes): Secretary
    {
        $secretary->update($attributes);

        return $secretary;
    }

    public function deactivate(Secretary $secretary): Secretary
    {
        return $this->changeStatus($secretary, SecretaryStatus::Inactive, 'Secrétaire désactivée');
    }

    public function reactivate(Secretary $secretary): Secretary
    {
        return $this->changeStatus($secretary, SecretaryStatus::Active, 'Secrétaire réactivée');
    }

    /**
     * Affecte automatiquement le rôle « secretary » au compte utilisateur lié.
     */
    public function ensureSecretaryRole(Secretary $secretary): void
    {
        $user = $secretary->user;

        if (! $user || $user->hasRole(Role::Secretary->value)) {
            return;
        }

        RoleModel::findOrCreate(Role::Secretary->value);

        $user->assignRole(Role::Secretary->value);
    }

    private function changeStatus(Secretary $secretary, SecretaryStatus $status, string $description): Secretary
    {
        $secretary->update(['status' => $status]);

        activity('secretaries')
            ->performedOn($secretary)
            ->causedBy(Auth::user())
            ->log($description);

        return $secretary;
    }
}
