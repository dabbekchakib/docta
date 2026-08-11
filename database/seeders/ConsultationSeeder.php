<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\ConsultationStatus;
use App\Enums\DoctorStatus;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\VitalSign;
use Illuminate\Database\Seeder;

class ConsultationSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = Doctor::query()
            ->where('status', DoctorStatus::Active->value)
            ->orderBy('id')
            ->get();

        if ($doctors->isEmpty()) {
            $doctors = Doctor::factory()->count(6)->create();
        }

        $patients = Patient::query()->orderBy('id')->get();

        if ($patients->isEmpty()) {
            $patients = Patient::factory()->count(60)->create();
        }

        $this->ensureAppointments($patients, $doctors);

        $appointments = Appointment::query()
            ->whereIn('status', [
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::Waiting->value,
                AppointmentStatus::InProgress->value,
                AppointmentStatus::Completed->value,
            ])
            ->inRandomOrder()
            ->limit(200)
            ->get();

        foreach ($appointments as $appointment) {
            $consultation = Consultation::factory()
                ->forAppointment($appointment)
                ->create([
                    'status' => $this->weightedStatus(),
                    'diagnosis' => $this->diagnosisFor($appointment),
                ]);

            if ($consultation->status !== ConsultationStatus::Cancelled && $this->shouldAddVitalSigns()) {
                VitalSign::factory()->create([
                    'consultation_id' => $consultation->id,
                ]);
            }
        }
    }

    private function shouldAddVitalSigns(): bool
    {
        return rand(1, 10) <= 8;
    }

    private function ensureAppointments($patients, $doctors): void
    {
        $count = Appointment::query()
            ->whereIn('status', [AppointmentStatus::Confirmed->value, AppointmentStatus::Completed->value])
            ->count();

        if ($count >= 200) {
            return;
        }

        Appointment::factory()->count(200 - $count)->create([
            'doctor_id' => $doctors->random()->id,
            'patient_id' => $patients->random()->id,
            'status' => AppointmentStatus::Confirmed,
            'appointment_date' => now()->subDays(rand(1, 60))->format('Y-m-d'),
        ]);
    }

    private function weightedStatus(): ConsultationStatus
    {
        return collect([
            ConsultationStatus::Completed, ConsultationStatus::Completed, ConsultationStatus::Completed,
            ConsultationStatus::Completed, ConsultationStatus::Completed,
            ConsultationStatus::InProgress,
            ConsultationStatus::Scheduled,
            ConsultationStatus::Cancelled,
        ])->random();
    }

    private function diagnosisFor(Appointment $appointment): string
    {
        return '<p>Consultation du '.($appointment->appointment_date?->format('d/m/Y') ?? '—').' : rapport établi par le médecin.</p>';
    }
}
