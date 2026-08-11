<?php

namespace Database\Factories;

use App\Enums\ConsultationStatus;
use App\Enums\ConsultationType;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consultation>
 */
class ConsultationFactory extends Factory
{
    protected $model = Consultation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->weightedStatus();
        $date = $this->faker->dateTimeBetween('-2 months', '+1 month');

        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'appointment_id' => null,
            'consultation_date' => $date->format('Y-m-d'),
            'start_time' => $this->faker->randomElement(['08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '14:00', '14:30', '15:00', '15:30', '16:00']),
            'end_time' => null,
            'type' => $this->faker->randomElement([
                ConsultationType::FirstVisit, ConsultationType::FirstVisit, ConsultationType::FirstVisit,
                ConsultationType::Control, ConsultationType::FollowUp,
                ConsultationType::Emergency, ConsultationType::Teleconsultation,
            ]),
            'reason' => $this->faker->randomElement([
                'Consultation générale',
                'Douleurs abdominales',
                'Suivi de tension artérielle',
                'Renouvellement d\'ordonnance',
                'Maux de tête récurrents',
                'Fièvre persistante',
                'Douleurs articulaires',
                'Bilan de contrôle',
            ]),
            'symptoms' => $this->faker->boolean(70) ? $this->faker->sentence(12) : null,
            'clinical_examination' => $this->faker->boolean(65) ? '<p>'.$this->faker->sentence(14).'</p>' : null,
            'diagnosis' => $this->faker->boolean(75) ? '<p>'.$this->faker->randomElement([
                'Rhinite allergique',
                'Gastro-entérite aiguë',
                'Hypertension artérielle essentielle',
                'Lombalgie commune',
                'Infection urinaire',
                'Migraine',
                'Anémie ferriprive',
                'Diabète de type 2 déséquilibré',
                'Pharyngite aiguë',
                'Dermatite de contact',
            ]).'</p>' : null,
            'secondary_diagnoses' => $this->faker->boolean(20) ? $this->faker->sentence(6) : null,
            'medical_notes' => $this->faker->boolean(40) ? '<p>'.$this->faker->paragraph(2).'</p>' : null,
            'treatment_plan' => $this->faker->boolean(70) ? '<p>'.$this->faker->paragraph(3).'</p>' : null,
            'recommendations' => $this->faker->boolean(70) ? '<p>'.$this->faker->sentence(15).'</p>' : null,
            'follow_up_date' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('+1 week', '+3 months')->format('Y-m-d') : null,
            'status' => $status,
            'started_at' => in_array($status, [ConsultationStatus::InProgress, ConsultationStatus::Completed], true)
                ? $date->format('Y-m-d 09:00:00')
                : null,
            'completed_at' => $status === ConsultationStatus::Completed
                ? $date->format('Y-m-d 09:30:00')
                : null,
            'cancelled_at' => $status === ConsultationStatus::Cancelled ? $date : null,
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }

    private function weightedStatus(): ConsultationStatus
    {
        return $this->faker->randomElement([
            ConsultationStatus::Scheduled, ConsultationStatus::Scheduled,
            ConsultationStatus::InProgress,
            ConsultationStatus::Completed, ConsultationStatus::Completed, ConsultationStatus::Completed,
            ConsultationStatus::Cancelled,
        ]);
    }

    /**
     * Lie la consultation à un rendez-vous existant.
     */
    public function forAppointment(Appointment $appointment): static
    {
        return $this->state(fn (): array => [
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'consultation_date' => $appointment->appointment_date?->toDateString(),
            'start_time' => $appointment->start_time,
            'status' => ConsultationStatus::InProgress,
        ]);
    }
}
