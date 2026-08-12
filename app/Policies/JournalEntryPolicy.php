<?php

namespace App\Policies;

use App\Enums\JournalEntryStatus;
use App\Enums\Permission;
use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::AccountingView->value,
            Permission::AccountingCreate->value,
            Permission::AccountingCancel->value,
        ]);
    }

    public function view(User $user, JournalEntry $entry): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::AccountingCreate->value);
    }

    public function update(User $user, JournalEntry $entry): bool
    {
        return $this->create($user) && $entry->status === JournalEntryStatus::Draft;
    }

    public function post(User $user, JournalEntry $entry): bool
    {
        return $this->create($user) && $entry->status === JournalEntryStatus::Draft;
    }

    public function cancel(User $user, JournalEntry $entry): bool
    {
        if (! $user->hasPermissionTo(Permission::AccountingCancel->value)) {
            return false;
        }

        return $entry->status === JournalEntryStatus::Draft;
    }

    public function delete(User $user, JournalEntry $entry): bool
    {
        return $entry->status === JournalEntryStatus::Draft;
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::AccountingCancel->value);
    }

    public function restore(User $user, JournalEntry $entry): bool
    {
        return $entry->status === JournalEntryStatus::Draft;
    }

    public function forceDelete(User $user, JournalEntry $entry): bool
    {
        return $this->delete($user, $entry);
    }
}
