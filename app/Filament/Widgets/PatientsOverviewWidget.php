<?php

namespace App\Filament\Widgets;

use App\Enums\PatientGender;
use App\Enums\PatientStatus;
use App\Models\Patient;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PatientsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $total = Patient::query()->count();
        $newThisMonth = Patient::query()->where('created_at', '>=', now()->startOfMonth())->count();
        $active = Patient::query()->where('status', PatientStatus::Active)->count();
        $archived = Patient::query()->where('status', PatientStatus::Archived)->count();
        $males = Patient::query()->where('gender', PatientGender::Male)->count();
        $females = $total - $males;

        return [
            Stat::make('Patients', $total)
                ->description('Total des dossiers patients')
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->icon(Heroicon::OutlinedIdentification)
                ->color('primary'),
            Stat::make('Nouveaux ce mois', $newThisMonth)
                ->description('Inscrits depuis le début du mois')
                ->descriptionIcon(Heroicon::OutlinedArrowTrendingUp)
                ->icon(Heroicon::OutlinedUserPlus)
                ->color('info'),
            Stat::make('Hommes / Femmes', $males.' / '.$females)
                ->description('Répartition par sexe')
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->icon(Heroicon::OutlinedUsers)
                ->color('warning'),
            Stat::make('Patients actifs', $active)
                ->description('Dossiers actifs')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->icon(Heroicon::OutlinedUserCircle)
                ->color('success'),
            Stat::make('Patients archivés', $archived)
                ->description('Dossiers archivés')
                ->descriptionIcon(Heroicon::OutlinedArchiveBox)
                ->icon(Heroicon::OutlinedArchiveBox)
                ->color('gray'),
        ];
    }
}
