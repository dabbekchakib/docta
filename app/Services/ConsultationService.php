<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\ConsultationStatus;
use App\Enums\ConsultationType;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ConsultationService
{
    public function __construct(private readonly AppointmentService $appointments) {}

    /**
     * Génère le numéro de consultation unique (CONS-000001).
     */
    public function generateConsultationNumber(): string
    {
        $sequence = Consultation::withTrashed()->max('id') ?? 0;

        return 'CONS-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crée une consultation médicale.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?User $createdBy = null): Consultation
    {
        $consultation = Consultation::create([
            ...$data,
            'created_by' => $createdBy?->id ?? Auth::id(),
        ]);

        return $consultation;
    }

    /**
     * Démarre une consultation depuis un rendez-vous :
     * crée la consultation et passe le rendez-vous en cours.
     */
    public function startFromAppointment(Appointment $appointment, ?User $createdBy = null): Consultation
    {
        $consultation = Consultation::create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'consultation_date' => $appointment->appointment_date?->toDateString() ?? now()->toDateString(),
            'start_time' => $appointment->start_time,
            'reason' => $appointment->reason,
            'type' => $this->mapAppointmentType($appointment->type),
            'status' => ConsultationStatus::InProgress,
            'started_at' => now(),
            'created_by' => $createdBy?->id ?? Auth::id(),
        ]);

        if ($appointment->status === AppointmentStatus::Completed) {
            $appointment->completed_at = null;
        }

        $this->appointments->changeStatus($appointment, AppointmentStatus::InProgress);

        return $consultation;
    }

    /**
     * Termine la consultation et marque le rendez-vous comme terminé.
     */
    public function complete(Consultation $consultation): Consultation
    {
        $consultation->fill([
            'status' => ConsultationStatus::Completed,
            'completed_at' => now(),
            'end_time' => $consultation->end_time ?? now()->format('H:i'),
        ]);
        $consultation->saveQuietly();

        $this->log($consultation, 'Consultation terminée');

        if ($consultation->appointment) {
            $this->appointments->changeStatus($consultation->appointment, AppointmentStatus::Completed);
        }

        return $consultation;
    }

    /**
     * Annule la consultation.
     */
    public function cancel(Consultation $consultation, ?string $reason = null): Consultation
    {
        $consultation->fill([
            'status' => ConsultationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
        $consultation->saveQuietly();

        $this->log($consultation, 'Consultation annulée'.($reason ? " : {$reason}" : ''));

        return $consultation;
    }

    /**
     * Convertit le type de rendez-vous en type de consultation.
     */
    public function mapAppointmentType(?AppointmentType $type): ConsultationType
    {
        return match ($type) {
            AppointmentType::Control => ConsultationType::Control,
            AppointmentType::FollowUp => ConsultationType::FollowUp,
            AppointmentType::Urgent => ConsultationType::Emergency,
            AppointmentType::Teleconsultation => ConsultationType::Teleconsultation,
            default => ConsultationType::FirstVisit,
        };
    }

    private function log(Consultation $consultation, string $description): void
    {
        activity('consultations')
            ->performedOn($consultation)
            ->causedBy(Auth::user())
            ->log($description);
    }
}
