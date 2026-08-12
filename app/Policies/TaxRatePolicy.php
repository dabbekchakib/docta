<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\TaxRate;
use App\Models\User;

class TaxRatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::TaxRatesView->value);
    }

    public function view(User $user, TaxRate $taxRate): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::TaxRatesManage->value);
    }

    public function update(User $user, TaxRate $taxRate): bool
    {
        return $user->hasPermissionTo(Permission::TaxRatesManage->value);
    }

    public function delete(User $user, TaxRate $taxRate): bool
    {
        return $user->hasPermissionTo(Permission::TaxRatesManage->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::TaxRatesManage->value);
    }
}
