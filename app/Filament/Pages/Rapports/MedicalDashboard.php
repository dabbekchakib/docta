<?php

namespace App\Filament\Pages\Rapports;

use App\Enums\ConsultationStatus;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Prescription;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MedicalDashboard extends Page
{
    protected string $view = 'filament.pages.rapports.medical-dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Rapports et tableaux de bord';

    protected static ?string $navigationLabel = 'Tableau de bord médical';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Tableau de bord médical';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'consultations.view',
            'consultations.manage',
            'reports.view',
        ]) ?? false;
    }

    public function getStats(): array
    {
        $consultationQuery = Consultation::query();
        $prescriptionQuery = Prescription::query();

        if (auth()->user()?->hasRole('doctor')) {
            $doctorId = Doctor::query()->where('user_id', auth()->id())->value('id');
            $consultationQuery->where('doctor_id', $doctorId ?: -1);
            $prescriptionQuery->where('doctor_id', $doctorId ?: -1);
        }

        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $todayConsultations = (clone $consultationQuery)->whereDate('consultation_date', $today)->count();
        $weekConsultations = (clone $consultationQuery)->whereBetween('consultation_date', [$weekStart, now()->endOfWeek()])->count();
        $monthConsultations = (clone $consultationQuery)->whereBetween('consultation_date', [$monthStart, now()->endOfMonth()])->count();
        $totalConsultations = (clone $consultationQuery)->count();

        $completedToday = (clone $consultationQuery)->whereDate('consultation_date', $today)->where('status', ConsultationStatus::Completed)->count();
        $cancelledToday = (clone $consultationQuery)->whereDate('consultation_date', $today)->where('status', ConsultationStatus::Cancelled)->count();

        $monthPrescriptions = (clone $prescriptionQuery)->whereBetween('prescription_date', [$monthStart, now()->endOfMonth()])->count();

        $consultationTypes = (clone $consultationQuery)
            ->whereBetween('consultation_date', [$monthStart, now()->endOfMonth()])
            ->get()
            ->groupBy(fn (Consultation $c) => $c->type?->label() ?? 'Non défini')
            ->map(fn ($items) => $items->count())
            ->sortDesc()
            ->toArray();

        $dailyConsultations = (clone $consultationQuery)
            ->whereBetween('consultation_date', [$monthStart, now()->endOfMonth()])
            ->get()
            ->groupBy(fn (Consultation $c) => $c->consultation_date->format('d/m'))
            ->map(fn ($items) => $items->count())
            ->toArray();

        $topDoctors = (clone $consultationQuery)
            ->whereBetween('consultation_date', [$monthStart, now()->endOfMonth()])
            ->where('status', ConsultationStatus::Completed)
            ->with('doctor')
            ->get()
            ->groupBy(fn (Consultation $c) => $c->doctor?->full_name ?? '—')
            ->map(fn ($items) => $items->count())
            ->sortDesc()
            ->take(5)
            ->toArray();

        return [
            'today' => $todayConsultations,
            'week' => $weekConsultations,
            'month' => $monthConsultations,
            'total' => $totalConsultations,
            'completed_today' => $completedToday,
            'cancelled_today' => $cancelledToday,
            'month_prescriptions' => $monthPrescriptions,
            'consultation_types' => $consultationTypes,
            'daily_consultations' => $dailyConsultations,
            'top_doctors' => $topDoctors,
        ];
    }
}
