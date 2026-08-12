<?php

namespace App\Policies;

use App\Enums\CreditNoteStatus;
use App\Enums\Permission;
use App\Models\CreditNote;
use App\Models\User;

class CreditNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::CreditNotesView->value,
            Permission::CreditNotesCreate->value,
        ]);
    }

    public function view(User $user, CreditNote $creditNote): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CreditNotesCreate->value);
    }

    public function issue(User $user, CreditNote $creditNote): bool
    {
        if (! $user->hasPermissionTo(Permission::CreditNotesCreate->value)) {
            return false;
        }

        return $creditNote->status === CreditNoteStatus::Draft;
    }

    public function cancel(User $user, CreditNote $creditNote): bool
    {
        if (! $user->hasPermissionTo(Permission::CreditNotesCancel->value)) {
            return false;
        }

        return in_array($creditNote->status, [CreditNoteStatus::Draft, CreditNoteStatus::Issued], true);
    }

    public function download(User $user, CreditNote $creditNote): bool
    {
        return $user->hasAnyPermission([
            Permission::CreditNotesView->value,
            Permission::CreditNotesCreate->value,
        ]);
    }
}
