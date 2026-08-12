<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\PaymentMethod;
use App\Models\User;

class PaymentMethodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PaymentMethodsView->value);
    }

    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PaymentMethodsManage->value);
    }

    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->hasPermissionTo(Permission::PaymentMethodsManage->value);
    }

    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->hasPermissionTo(Permission::PaymentMethodsManage->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PaymentMethodsManage->value);
    }
}
