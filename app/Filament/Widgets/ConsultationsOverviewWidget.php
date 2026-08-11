<?php

namespace App\Filament\Widgets;

use App\Models\Consultation;
use App\Models\Doctor;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ConsultationsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $query = Consultation::query();

        if (auth()->user()?->hasRole('doctor')) {
            $doctorId = Doctor::query()->where('user_id', auth()->id())->value('id');
            $query->where('doctor_id', $doctorId ?: -1);
        }

        $today = (clone $query)->whereDate('consultation_date', today())->count();
        $thisWeek = (clone $query)->whereBetween('consultation_date', [today()->startOfWeek(), today()->endOfWeek()])->count();
        $patientsSeen = (clone $query)->where('status', 'completed')->distinct('patient_id')->count('patient_id');
        $topDiagnosis = (clone $query)
            ->whereNotNull('diagnosis')
            ->get()
            ->map(fn (Consultation $consultation): string => strip_tags((string) $consultation->diagnosis))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return [
            Stat::make('Consultations aujourd\'hui', $today)
                ->description('Réalisées ce jour')
                ->descriptionIcon(Heroicon::OutlinedClipboardDocument)
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->color('primary'),
            Stat::make('Cette semaine', $thisWeek)
                ->description('Du '.today()->startOfWeek()->format('d/m').' au '.today()->endOfWeek()->format('d/m'))
                ->descriptionIcon(Heroicon::OutlinedCalendarDays)
                ->icon(Heroicon::OutlinedClipboardDocument)
                ->color('info'),
            Stat::make('Patients vus', $patientsSeen)
                ->description('Consultations terminées')
                ->descriptionIcon(Heroicon::OutlinedIdentification)
                ->icon(Heroicon::OutlinedUserGroup)
                ->color('success'),
            Stat::make('Diagnostic fréquent', $topDiagnosis ?: '—')
                ->description('Sur les consultations terminées')
                ->descriptionIcon(Heroicon::OutlinedDocumentText)
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('warning'),
        ];
    }
}
