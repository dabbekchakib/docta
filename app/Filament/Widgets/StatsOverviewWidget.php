<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $users = User::query();
        $rolesCount = Role::count();
        $permissionsCount = Permission::count();

        return [
            Stat::make('Utilisateurs', $users->count())
                ->description('Nombre total d\'utilisateurs')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->icon(Heroicon::OutlinedUserGroup)
                ->color('primary'),
            Stat::make('Utilisateurs vérifiés', (clone $users)->whereNotNull('email_verified_at')->count())
                ->description('Comptes email vérifiés')
                ->descriptionIcon(Heroicon::OutlinedCheckBadge)
                ->icon(Heroicon::OutlinedUsers)
                ->color('success'),
            Stat::make('Rôles', $rolesCount)
                ->description('Rôles disponibles')
                ->descriptionIcon(Heroicon::OutlinedShieldCheck)
                ->icon(Heroicon::OutlinedShieldCheck)
                ->color('warning'),
            Stat::make('Permissions', $permissionsCount)
                ->description('Permissions configurées')
                ->descriptionIcon(Heroicon::OutlinedKey)
                ->icon(Heroicon::OutlinedKey)
                ->color('danger'),
        ];
    }
}
