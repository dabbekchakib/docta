<?php

namespace App\Filament\Patient\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Patient;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PatientStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $patient = $this->getPatient();

        if (! $patient) {
            return [
                Stat::make('Rendez-vous', 0)
                    ->description('Aucun patient associé')
                    ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                    ->color('gray'),
            ];
        }

        $totalAppointments = $patient->appointments()->count();
        $totalConsultations = $patient->consultations()->count();
        $totalPrescriptions = $patient->prescriptions()->count();
        $totalInvoices = $patient->invoices()->count();

        return [
            Stat::make('Rendez-vous', $totalAppointments)
                ->description('Total')
                ->descriptionIcon(Heroicon::OutlinedCalendar)
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('primary'),
            Stat::make('Consultations', $totalConsultations)
                ->description('Total')
                ->descriptionIcon(Heroicon::OutlinedClipboardDocumentList)
                ->icon(Heroicon::OutlinedStethoscope)
                ->color('info'),
            Stat::make('Ordonnances', $totalPrescriptions)
                ->description('Total')
                ->descriptionIcon(Heroicon::OutlinedDocumentText)
                ->icon(Heroicon::OutlinedDocumentCheck)
                ->color('success'),
            Stat::make('Factures', $totalInvoices)
                ->description('Total')
                ->descriptionIcon(Heroicon::OutlinedReceiptPercent)
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('warning'),
        ];
    }

    protected function getPatient(): ?Patient
    {
        return auth()->user()->patient;
    }
}
