<?php

namespace App\Filament\Pages\Rapports;

use App\Enums\AppointmentStatus;
use App\Enums\ConsultationStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Payment;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ActivityDashboard extends Page
{
    protected string $view = 'filament.pages.rapports.activity-dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Rapports et tableaux de bord';

    protected static ?string $navigationLabel = 'Tableau de bord activités';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Tableau de bord activités';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'consultations.view',
            'appointments.manage',
            'reports.view',
        ]) ?? false;
    }

    public function getStats(): array
    {
        $monthStart = now()->startOfMonth();

        $appointmentQuery = Appointment::query();
        $consultationQuery = Consultation::query();

        if (auth()->user()?->hasRole('doctor')) {
            $doctorId = Doctor::query()->where('user_id', auth()->id())->value('id');
            $appointmentQuery->where('doctor_id', $doctorId ?: -1);
            $consultationQuery->where('doctor_id', $doctorId ?: -1);
        }

        $monthAppointments = (clone $appointmentQuery)->whereBetween('appointment_date', [$monthStart, now()->endOfMonth()])->count();
        $monthCompleted = (clone $appointmentQuery)->whereBetween('appointment_date', [$monthStart, now()->endOfMonth()])->where('status', AppointmentStatus::Completed)->count();
        $monthCancelled = (clone $appointmentQuery)->whereBetween('appointment_date', [$monthStart, now()->endOfMonth()])->where('status', AppointmentStatus::Cancelled)->count();
        $monthAbsent = (clone $appointmentQuery)->whereBetween('appointment_date', [$monthStart, now()->endOfMonth()])->where('status', AppointmentStatus::Absent)->count();

        $monthConsultations = (clone $consultationQuery)->whereBetween('consultation_date', [$monthStart, now()->endOfMonth()])->count();
        $completedConsultations = (clone $consultationQuery)->whereBetween('consultation_date', [$monthStart, now()->endOfMonth()])->where('status', ConsultationStatus::Completed)->count();

        $noShowRate = $monthAppointments > 0
            ? round(($monthAbsent / $monthAppointments) * 100, 1)
            : 0;
        $completionRate = $monthAppointments > 0
            ? round(($monthCompleted / $monthAppointments) * 100, 1)
            : 0;

        $dailyAppointments = (clone $appointmentQuery)
            ->whereBetween('appointment_date', [$monthStart, now()->endOfMonth()])
            ->get()
            ->groupBy(fn (Appointment $a) => $a->appointment_date->format('d/m'))
            ->map(fn ($items) => $items->count())
            ->toArray();

        $statusDistribution = (clone $appointmentQuery)
            ->whereBetween('appointment_date', [$monthStart, now()->endOfMonth()])
            ->get()
            ->groupBy(fn (Appointment $a) => $a->status?->label() ?? 'Non défini')
            ->map(fn ($items) => $items->count())
            ->toArray();

        $topDoctors = (clone $appointmentQuery)
            ->whereBetween('appointment_date', [$monthStart, now()->endOfMonth()])
            ->with('doctor')
            ->get()
            ->groupBy(fn (Appointment $a) => $a->doctor?->full_name ?? '—')
            ->map(fn ($items) => $items->count())
            ->sortDesc()
            ->take(5)
            ->toArray();

        $monthRevenue = Invoice::query()
            ->whereBetween('invoice_date', [$monthStart, now()->endOfMonth()])
            ->sum('total');

        $monthCollected = Payment::query()
            ->where('status', 'validated')
            ->whereBetween('payment_date', [$monthStart, now()->endOfMonth()])
            ->sum('amount');

        return [
            'month_appointments' => $monthAppointments,
            'month_completed' => $monthCompleted,
            'month_cancelled' => $monthCancelled,
            'month_absent' => $monthAbsent,
            'month_consultations' => $monthConsultations,
            'completed_consultations' => $completedConsultations,
            'no_show_rate' => $noShowRate,
            'completion_rate' => $completionRate,
            'daily_appointments' => $dailyAppointments,
            'status_distribution' => $statusDistribution,
            'top_doctors' => $topDoctors,
            'month_revenue' => $monthRevenue,
            'month_collected' => $monthCollected,
        ];
    }
}
