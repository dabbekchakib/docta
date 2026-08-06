<?php

namespace App\Filament\Widgets;

use App\Enums\DoctorStatus;
use App\Models\Doctor;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DoctorsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $total = Doctor::query()->count();
        $active = Doctor::query()->where('status', DoctorStatus::Active)->count();
        $inactive = $total - $active;
        $hiredThisYear = Doctor::query()
            ->where('start_working_date', '>=', now()->startOfYear())
            ->count();

        return [
            Stat::make('Médecins', $total)
                ->description('Nombre total de médecins')
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->icon(Heroicon::OutlinedUserCircle)
                ->color('primary'),
            Stat::make('Médecins actifs', $active)
                ->description('Médecins en activité')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->icon(Heroicon::OutlinedUserCircle)
                ->color('success'),
            Stat::make('Médecins inactifs', $inactive)
                ->description('Médecins désactivés')
                ->descriptionIcon(Heroicon::OutlinedPauseCircle)
                ->icon(Heroicon::OutlinedUserCircle)
                ->color('warning'),
            Stat::make('Recrutements cette année', $hiredThisYear)
                ->description('Depuis le début de l\'année')
                ->descriptionIcon(Heroicon::OutlinedArrowTrendingUp)
                ->icon(Heroicon::OutlinedAcademicCap)
                ->color('info'),
        ];
    }
}
