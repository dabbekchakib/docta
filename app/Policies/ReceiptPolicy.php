<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Receipt;
use App\Models\User;

class ReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::ReceiptsView->value,
            Permission::ReceiptsCreate->value,
        ]);
    }

    public function view(User $user, Receipt $receipt): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ReceiptsCreate->value);
    }

    public function download(User $user, Receipt $receipt): bool
    {
        return $user->hasPermissionTo(Permission::ReceiptsDownload->value);
    }
}
