<?php

namespace App\Policies;

use App\Enums\LaboratoryRequestStatus;
use App\Enums\Permission;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class LaboratoryRequestPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::LaboratoryRequestsView->value,
            Permission::LaboratoryRequestsCreate->value,
            Permission::LaboratoryRequestsUpdate->value,
            Permission::LaboratoryRequestsCancel->value,
        ]);
    }

    public function view(User $user, LaboratoryRequest $request): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($this->isSecretary($user)) {
            return true;
        }

        if ($this->isDoctor($user)) {
            return $request->doctor_id === $this->doctorIdFor($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::LaboratoryRequestsCreate->value);
    }

    public function update(User $user, LaboratoryRequest $request): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryRequestsUpdate->value)) {
            return false;
        }

        if (! $request->isEditable()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $request->doctor_id === $this->doctorIdFor($user);
    }

    public function delete(User $user, LaboratoryRequest $request): bool
    {
        return $this->update($user, $request);
    }

    public function deleteAny(User $user): bool
    {
        return $this->delete($user, app(LaboratoryRequest::class));
    }

    public function submit(User $user, LaboratoryRequest $request): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryRequestsUpdate->value)) {
            return false;
        }

        if ($request->status !== LaboratoryRequestStatus::Draft) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $request->doctor_id === $this->doctorIdFor($user);
    }

    public function accept(User $user, LaboratoryRequest $request): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryRequestsUpdate->value)) {
            return false;
        }

        return $request->status === LaboratoryRequestStatus::Requested;
    }

    /**
     * Gestion laboratoire (prélèvements, réception, traitement).
     */
    public function manageSamples(User $user, LaboratoryRequest $request): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryRequestsUpdate->value)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $request->doctor_id === $this->doctorIdFor($user);
    }

    /**
     * Saisie des résultats : réservée aux utilisateurs autorisés sur une
     * demande non encore validée ni annulée.
     */
    public function enterResults(User $user, LaboratoryRequest $request): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryResultsCreate->value)) {
            return false;
        }

        return ! $request->isValidated() && $request->status !== LaboratoryRequestStatus::Cancelled;
    }

    /**
     * Validation biologique : nécessite des résultats complets sur la demande.
     */
    public function validateResults(User $user, LaboratoryRequest $request): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryResultsValidate->value)) {
            return false;
        }

        if ($request->isValidated()) {
            return false;
        }

        return $request->results()->exists() && $request->allItemsHaveResults();
    }

    public function cancel(User $user, LaboratoryRequest $request): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryRequestsCancel->value)) {
            return false;
        }

        if (in_array($request->status, [LaboratoryRequestStatus::Cancelled, LaboratoryRequestStatus::Completed], true)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $request->doctor_id === $this->doctorIdFor($user);
    }

    public function restore(User $user, LaboratoryRequest $request): bool
    {
        return $this->delete($user, $request);
    }

    public function forceDelete(User $user, LaboratoryRequest $request): bool
    {
        return $this->delete($user, $request);
    }

    private function doctorIdFor(User $user): ?int
    {
        return Doctor::query()->where('user_id', $user->id)->value('id');
    }
}
