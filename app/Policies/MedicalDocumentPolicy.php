<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\MedicalDocument;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class MedicalDocumentPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::MedicalDocumentsView->value);
    }

    public function view(User $user, MedicalDocument $document): bool
    {
        if (! $user->hasPermissionTo(Permission::MedicalDocumentsView->value)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isDoctor($user) && $this->isDoctorOfPatient($user, $this->medicalRecordOf($document));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::MedicalDocumentsCreate->value);
    }

    public function delete(User $user, MedicalDocument $document): bool
    {
        return $user->hasPermissionTo(Permission::MedicalDocumentsDelete->value);
    }

    public function deleteAny(User $user): bool
    {
        return $this->delete($user, app(MedicalDocument::class));
    }

    public function download(User $user, MedicalDocument $document): bool
    {
        return $this->view($user, $document) && $user->hasPermissionTo(Permission::MedicalDocumentsDownload->value);
    }
}
