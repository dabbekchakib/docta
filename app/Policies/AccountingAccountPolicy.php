<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\AccountingAccount;
use App\Models\User;

class AccountingAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::AccountingView->value,
            Permission::AccountingAccountsManage->value,
        ]);
    }

    public function view(User $user, AccountingAccount $account): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::AccountingAccountsManage->value);
    }

    public function update(User $user, AccountingAccount $account): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, AccountingAccount $account): bool
    {
        if ($account->is_system) {
            return false;
        }

        return $this->create($user);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::AccountingAccountsManage->value);
    }

    public function restore(User $user, AccountingAccount $account): bool
    {
        return $this->create($user);
    }

    public function forceDelete(User $user, AccountingAccount $account): bool
    {
        return $this->delete($user, $account);
    }
}
