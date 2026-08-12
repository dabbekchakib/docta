<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryResult;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class LaboratoryResultPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::LaboratoryResultsView->value,
            Permission::LaboratoryResultsCreate->value,
            Permission::LaboratoryResultsUpdate->value,
            Permission::LaboratoryResultsValidate->value,
        ]);
    }

    /**
     * Les résultats sont des données médicales sensibles : le secrétaire
     * ne peut pas les consulter sans permission explicite.
     */
    public function view(User $user, LaboratoryResult $result): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryResultsView->value)) {
            return false;
        }

        $request = $result->item?->request;

        if (! $request) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $request->doctor_id === $this->doctorIdFor($user);
    }

    public function viewForRequest(User $user, LaboratoryRequest $request): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryResultsView->value)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $request->doctor_id === $this->doctorIdFor($user);
    }

    public function create(User $user, LaboratoryRequest $request): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryResultsCreate->value)) {
            return false;
        }

        return ! $request->isValidated() && $request->status !== \App\Enums\LaboratoryRequestStatus::Cancelled;
    }

    public function update(User $user, LaboratoryResult $result): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryResultsUpdate->value)) {
            return false;
        }

        return ! $result->isValidated();
    }

    public function validate(User $user, LaboratoryRequest $request): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryResultsValidate->value)) {
            return false;
        }

        if ($request->isValidated()) {
            return false;
        }

        return $request->results()->exists() && $request->allItemsHaveResults();
    }

    public function delete(User $user, LaboratoryResult $result): bool
    {
        return $this->update($user, $result);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::LaboratoryResultsUpdate->value);
    }

    public function restore(User $user, LaboratoryResult $result): bool
    {
        return $this->update($user, $result);
    }

    public function forceDelete(User $user, LaboratoryResult $result): bool
    {
        return $this->update($user, $result);
    }

    private function doctorIdFor(User $user): ?int
    {
        return Doctor::query()->where('user_id', $user->id)->value('id');
    }
}
