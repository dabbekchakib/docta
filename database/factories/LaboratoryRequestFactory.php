<?php

namespace Database\Factories;

use App\Enums\LaboratoryRequestPriority;
use App\Enums\LaboratoryRequestStatus;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Laboratory;
use App\Models\LaboratoryRequest;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryRequest>
 */
class LaboratoryRequestFactory extends Factory
{
    protected $model = LaboratoryRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_number' => 'LAB-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'consultation_id' => null,
            'laboratory_id' => Laboratory::factory(),
            'requested_at' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'priority' => $this->faker->randomElement(LaboratoryRequestPriority::cases()),
            'status' => $this->faker->randomElement(LaboratoryRequestStatus::cases()),
            'clinical_information' => $this->faker->boolean(40) ? $this->faker->sentence(8) : null,
            'doctor_notes' => $this->faker->boolean(30) ? $this->faker->sentence(6) : null,
            'patient_instructions' => $this->faker->boolean(30) ? 'À jeun avant le prélèvement' : null,
            'created_by' => null,
        ];
    }

    /**
     * Demande liée à une consultation cohérente (patient + médecin identiques).
     */
    public function forConsultation(Consultation $consultation): static
    {
        return $this->state(fn (): array => [
            'patient_id' => $consultation->patient_id,
            'doctor_id' => $consultation->doctor_id,
            'consultation_id' => $consultation->id,
            'requested_at' => $consultation->consultation_date?->toDateString(),
        ]);
    }
}
