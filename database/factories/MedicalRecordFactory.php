<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition(): array
    {
        return [
            'patient_id' => fn () => Patient::withoutEvents(function (): int {
                $patient = Patient::factory()->create([
                    'patient_number' => 'PAT-'.str_pad((string) ((Patient::withTrashed()->max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT),
                ]);

                return $patient->id;
            }),
            'blood_group' => $this->faker->randomElement(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']),
            'rh_factor' => $this->faker->randomElement(['+', '-']),
            'general_notes' => $this->faker->boolean(30) ? $this->faker->sentence(10) : null,
            'emergency_notes' => $this->faker->boolean(20) ? $this->faker->sentence(8) : null,
        ];
    }
}
