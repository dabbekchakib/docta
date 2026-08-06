<?php

namespace App\Support;

use App\Enums\Permission as PermissionEnum;
use Spatie\Permission\Models\Permission;

class Permissions
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return Permission::query()
            ->pluck('name')
            ->mapWithKeys(
                fn (string $name): array => [$name => PermissionEnum::tryFrom($name)?->label() ?? $name]
            )
            ->all();
    }
}
