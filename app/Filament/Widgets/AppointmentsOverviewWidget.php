<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AppointmentsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $query = Appointment::query();

        if (auth()->user()?->hasRole('doctor')) {
            $doctorId = Doctor::query()->where('user_id', auth()->id())->value('id');
            $query->where('doctor_id', $doctorId ?: -1);
        }

        $today = (clone $query)->whereDate('appointment_date', today())->count();
        $thisWeek = (clone $query)->whereBetween('appointment_date', [today()->startOfWeek(), today()->endOfWeek()])->count();
        $pending = (clone $query)->where('status', AppointmentStatus::Pending)->count();
        $confirmed = (clone $query)->where('status', AppointmentStatus::Confirmed)->count();

        return [
            Stat::make('Rendez-vous aujourd\'hui', $today)
                ->description('Prévus ce jour')
                ->descriptionIcon(Heroicon::OutlinedCalendar)
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('primary'),
            Stat::make('Cette semaine', $thisWeek)
                ->description('Du '.today()->startOfWeek()->format('d/m').' au '.today()->endOfWeek()->format('d/m'))
                ->descriptionIcon(Heroicon::OutlinedCalendarDays)
                ->icon(Heroicon::OutlinedCalendar)
                ->color('info'),
            Stat::make('En attente', $pending)
                ->description('À confirmer')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->icon(Heroicon::OutlinedClock)
                ->color('warning'),
            Stat::make('Confirmés', $confirmed)
                ->description('Rendez-vous confirmés')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success'),
        ];
    }
}
