<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\RefundStatus;
use App\Models\Refund;
use App\Models\User;

class RefundPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::RefundsView->value,
            Permission::RefundsCreate->value,
            Permission::RefundsApprove->value,
        ]);
    }

    public function view(User $user, Refund $refund): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::RefundsCreate->value);
    }

    public function approve(User $user, Refund $refund): bool
    {
        if (! $user->hasPermissionTo(Permission::RefundsApprove->value)) {
            return false;
        }

        return $refund->status === RefundStatus::Pending;
    }

    public function reject(User $user, Refund $refund): bool
    {
        if (! $user->hasPermissionTo(Permission::RefundsReject->value)) {
            return false;
        }

        return $refund->status === RefundStatus::Pending;
    }

    public function delete(User $user, Refund $refund): bool
    {
        if (! $user->hasPermissionTo(Permission::RefundsApprove->value)) {
            return false;
        }

        return in_array($refund->status, [RefundStatus::Pending, RefundStatus::Approved], true);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::RefundsApprove->value);
    }

    public function restore(User $user, Refund $refund): bool
    {
        return $this->delete($user, $refund);
    }

    public function forceDelete(User $user, Refund $refund): bool
    {
        return $user->hasPermissionTo(Permission::RefundsApprove->value);
    }
}
