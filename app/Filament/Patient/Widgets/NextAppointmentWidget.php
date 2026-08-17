<?php

namespace App\Filament\Patient\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Patient;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class NextAppointmentWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $patient = $this->getPatient();

        if (! $patient) {
            return [
                Stat::make('Prochain rendez-vous', '—')
                    ->description('Aucun patient associé')
                    ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                    ->color('gray'),
            ];
        }

        $nextAppointment = $patient->appointments()
            ->with('doctor')
            ->whereIn('status', [
                AppointmentStatus::Pending->value,
                AppointmentStatus::Confirmed->value,
            ])
            ->where('appointment_date', '>=', now()->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->first();

        if (! $nextAppointment) {
            return [
                Stat::make('Prochain rendez-vous', '—')
                    ->description('Aucun rendez-vous à venir')
                    ->descriptionIcon(Heroicon::OutlinedCalendar)
                    ->color('gray'),
            ];
        }

        $date = Carbon::parse($nextAppointment->appointment_date);
        $doctorName = $nextAppointment->doctor?->full_name ?? '—';

        return [
            Stat::make('Prochain rendez-vous', $date->translatedFormat('d M Y'))
                ->description("{$nextAppointment->start_time} — Dr. {$doctorName}")
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color($nextAppointment->status === AppointmentStatus::Confirmed ? 'success' : 'primary'),
        ];
    }

    protected function getPatient(): ?Patient
    {
        return auth()->user()->patient;
    }
}
