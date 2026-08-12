<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Enums\Permission;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::InvoicesView->value,
            Permission::InvoicesCreate->value,
            Permission::InvoicesUpdate->value,
            Permission::InvoicesIssue->value,
            Permission::InvoicesDownload->value,
        ]);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->isAdmin() || $user->hasRole('secretary') || $user->hasRole('accountant')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            if (! $invoice->doctor_id) {
                return false;
            }

            return $invoice->doctor_id === $this->doctorIdFor($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::InvoicesCreate->value);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if (! $user->hasPermissionTo(Permission::InvoicesUpdate->value)) {
            return false;
        }

        return $invoice->status === InvoiceStatus::Draft;
    }

    public function issue(User $user, Invoice $invoice): bool
    {
        if (! $user->hasPermissionTo(Permission::InvoicesIssue->value)) {
            return false;
        }

        return $invoice->status === InvoiceStatus::Draft;
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        if (! $user->hasPermissionTo(Permission::InvoicesCancel->value)) {
            return false;
        }

        return in_array($invoice->status, [
            InvoiceStatus::Draft,
            InvoiceStatus::Issued,
            InvoiceStatus::PartiallyPaid,
            InvoiceStatus::Overdue,
        ], true);
    }

    public function download(User $user, Invoice $invoice): bool
    {
        if (! $user->hasPermissionTo(Permission::InvoicesDownload->value)) {
            return false;
        }

        return $this->view($user, $invoice);
    }

    public function export(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo(Permission::InvoicesExport->value);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo(Permission::InvoicesCancel->value)
            && $invoice->status === InvoiceStatus::Draft;
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::InvoicesCancel->value);
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return $this->delete($user, $invoice);
    }

    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo(Permission::InvoicesCancel->value);
    }

    private function doctorIdFor(User $user): ?int
    {
        return Doctor::query()->where('user_id', $user->id)->value('id');
    }
}
