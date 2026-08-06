<?php

namespace App\Support;

use App\Enums\Role as RoleEnum;
use Spatie\Permission\Models\Role;

class Roles
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return Role::query()
            ->pluck('name')
            ->mapWithKeys(
                fn (string $name): array => [$name => RoleEnum::tryFrom($name)?->label() ?? $name]
            )
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function optionsById(): array
    {
        return Role::query()
            ->get()
            ->mapWithKeys(
                fn (Role $role): array => [$role->getKey() => RoleEnum::tryFrom($role->name)?->label() ?? $role->name]
            )
            ->all();
    }
}
