<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'appointment_date' => $this->faker->dateTimeBetween('-2 months', '+3 months')->format('Y-m-d'),
            'start_time' => $this->faker->randomElement([
                '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
                '11:00', '11:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30',
            ]),
            'duration' => $this->faker->randomElement([15, 20, 30, 45, 60]),
            'status' => $this->weightedStatus(),
            'type' => $this->faker->randomElement([
                AppointmentType::Consultation, AppointmentType::Consultation, AppointmentType::Consultation,
                AppointmentType::Control, AppointmentType::FollowUp,
                AppointmentType::Urgent, AppointmentType::Teleconsultation,
            ]),
            'reason' => $this->faker->randomElement([
                'Consultation générale',
                'Douleurs abdominales',
                'Suivi de tension',
                'Renouvellement d\'ordonnance',
                'Maux de tête récurrents',
                'Bilan de contrôle',
                'Fièvre persistante',
                'Consultation de routine',
            ]),
            'notes' => $this->faker->boolean(25) ? $this->faker->sentence(10) : null,
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }

    private function weightedStatus(): AppointmentStatus
    {
        return $this->faker->randomElement([
            AppointmentStatus::Pending, AppointmentStatus::Pending,
            AppointmentStatus::Confirmed, AppointmentStatus::Confirmed, AppointmentStatus::Confirmed,
            AppointmentStatus::Waiting,
            AppointmentStatus::InProgress,
            AppointmentStatus::Completed, AppointmentStatus::Completed,
            AppointmentStatus::Cancelled, AppointmentStatus::Absent,
        ]);
    }
}
