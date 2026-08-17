<?php

namespace App\Services\AI\Tools;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;

class GetStatsTool extends BaseAITool
{
    public function getName(): string
    {
        return 'get_stats';
    }

    public function getDescription(): string
    {
        return 'Obtient des statistiques globales ou pour une période donnée: nombre de patients, médecins, rendez-vous, consultations, factures, paiements.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'description' => 'Type de statistiques: general, today, month, year, doctor (par médecin)',
                ],
                'doctor_id' => [
                    'type' => 'integer',
                    'description' => 'ID du médecin (si type=doctor)',
                ],
            ],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['reports.view'];
    }

    public function execute(User $user, array $parameters): array
    {
        $type = $parameters['type'] ?? 'general';

        $data = match ($type) {
            'today' => $this->getTodayStats(),
            'month' => $this->getMonthStats(),
            'year' => $this->getYearStats(),
            'doctor' => $this->getDoctorStats($parameters['doctor_id'] ?? null),
            default => $this->getGeneralStats(),
        };

        $this->logActivity(
            $user,
            null,
            "Statistiques: {$type}",
            null,
            $parameters,
            'success',
            'Statistiques générées',
        );

        return $this->success($data, "Statistiques {$type} générées.");
    }

    private function getGeneralStats(): array
    {
        return [
            'total_patients' => Patient::count(),
            'total_doctors' => \App\Models\Doctor::count(),
            'total_secretaries' => \App\Models\Secretary::count(),
            'total_appointments' => Appointment::count(),
            'total_consultations' => Consultation::count(),
            'total_invoices' => Invoice::count(),
            'total_payments' => Payment::count(),
        ];
    }

    private function getTodayStats(): array
    {
        $today = now()->toDateString();

        return [
            'date' => $today,
            'appointments_today' => Appointment::where('appointment_date', $today)->count(),
            'consultations_today' => Consultation::where('consultation_date', $today)->count(),
            'invoices_today' => Invoice::whereDate('created_at', $today)->count(),
            'payments_today' => Payment::where('payment_date', $today)->sum('amount'),
        ];
    }

    private function getMonthStats(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return [
            'period' => $startOfMonth->format('m/Y'),
            'appointments' => Appointment::whereBetween('appointment_date', [$startOfMonth, $endOfMonth])->count(),
            'consultations' => Consultation::whereBetween('consultation_date', [$startOfMonth, $endOfMonth])->count(),
            'invoices_count' => Invoice::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'invoices_total' => Invoice::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total'),
            'payments_count' => Payment::whereBetween('payment_date', [$startOfMonth, $endOfMonth])->count(),
            'payments_total' => Payment::whereBetween('payment_date', [$startOfMonth, $endOfMonth])->sum('amount'),
            'new_patients' => Patient::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
        ];
    }

    private function getYearStats(): array
    {
        $year = now()->year;

        return [
            'year' => $year,
            'appointments' => Appointment::whereYear('appointment_date', $year)->count(),
            'consultations' => Consultation::whereYear('consultation_date', $year)->count(),
            'invoices_count' => Invoice::whereYear('created_at', $year)->count(),
            'invoices_total' => Invoice::whereYear('created_at', $year)->sum('total'),
            'payments_total' => Payment::whereYear('payment_date', $year)->sum('amount'),
            'new_patients' => Patient::whereYear('created_at', $year)->count(),
        ];
    }

    private function getDoctorStats(?int $doctorId): array
    {
        if (! $doctorId) {
            return ['error' => 'ID médecin requis pour ce type de statistiques.'];
        }

        $doctor = \App\Models\Doctor::find($doctorId);
        if (! $doctor) {
            return ['error' => 'Médecin non trouvé.'];
        }

        return [
            'doctor' => trim("{$doctor->first_name} {$doctor->last_name}"),
            'total_appointments' => $doctor->appointments()->count(),
            'appointments_this_month' => $doctor->appointments()
                ->whereMonth('appointment_date', now()->month)
                ->whereYear('appointment_date', now()->year)
                ->count(),
            'total_consultations' => $doctor->consultations()->count(),
            'consultations_this_month' => $doctor->consultations()
                ->whereMonth('consultation_date', now()->month)
                ->whereYear('consultation_date', now()->year)
                ->count(),
            'total_invoices' => $doctor->invoices()->count(),
            'invoices_total_amount' => $doctor->invoices()->sum('total'),
        ];
    }
}
