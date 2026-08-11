<?php

namespace Database\Factories;

use App\Enums\PrescriptionStatus;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement([
            PrescriptionStatus::Draft,
            PrescriptionStatus::Issued,
            PrescriptionStatus::Issued,
            PrescriptionStatus::Issued,
            PrescriptionStatus::Cancelled,
            PrescriptionStatus::Expired,
        ]);

        $prescriptionDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $validUntil = $this->faker->boolean(70)
            ? (clone $prescriptionDate)->modify('+'.rand(1, 30).' days')
            : null;

        return [
            'prescription_number' => 'ORD-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'consultation_id' => null,
            'prescription_date' => $prescriptionDate->format('Y-m-d'),
            'status' => $status,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence(8) : null,
            'valid_until' => $validUntil?->format('Y-m-d'),
            'verification_token' => Str::random(40),
            'created_by' => null,
        ];
    }

    /**
     * Ordonnance liée à une consultation cohérente (patient + médecin identiques).
     */
    public function forConsultation(Consultation $consultation): static
    {
        return $this->state(fn (): array => [
            'patient_id' => $consultation->patient_id,
            'doctor_id' => $consultation->doctor_id,
            'consultation_id' => $consultation->id,
            'prescription_date' => $consultation->consultation_date?->toDateString(),
        ]);
    }

    /**
     * Ordonnance émise avec un numéro unique.
     */
    public function issued(): static
    {
        return $this->state(fn (): array => [
            'status' => PrescriptionStatus::Issued,
            'verification_token' => Str::random(40),
        ]);
    }
}
