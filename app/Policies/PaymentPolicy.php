<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Enums\Permission;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::PaymentsView->value,
            Permission::PaymentsCreate->value,
            Permission::PaymentsUpdate->value,
            Permission::PaymentsValidate->value,
            Permission::PaymentsCancel->value,
        ]);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->viewAny($user)
            && ($user->isAdmin()
                || $user->hasRole('secretary')
                || $user->hasRole('accountant')
                || $payment->received_by === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PaymentsCreate->value);
    }

    public function update(User $user, Payment $payment): bool
    {
        if (! $user->hasPermissionTo(Permission::PaymentsUpdate->value)) {
            return false;
        }

        return $payment->status === PaymentStatus::Pending;
    }

    public function validate(User $user, Payment $payment): bool
    {
        if (! $user->hasPermissionTo(Permission::PaymentsValidate->value)) {
            return false;
        }

        return $payment->status === PaymentStatus::Pending;
    }

    public function cancel(User $user, Payment $payment): bool
    {
        if (! $user->hasPermissionTo(Permission::PaymentsCancel->value)) {
            return false;
        }

        return in_array($payment->status, [
            PaymentStatus::Pending,
            PaymentStatus::Completed,
        ], true);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->cancel($user, $payment);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PaymentsCancel->value);
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $this->cancel($user, $payment);
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo(Permission::PaymentsCancel->value);
    }
}
