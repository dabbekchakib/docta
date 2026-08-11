<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Vaccination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vaccination>
 */
class VaccinationFactory extends Factory
{
    protected $model = Vaccination::class;

    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'vaccine_name' => $this->faker->randomElement([
                'BCG',
                'VHB',
                'DTCoq-Hib-HepB',
                'ROR',
                'Pneumocoque',
                'Grippe saisonnière',
                'Covid-19',
                'Tétanos',
            ]),
            'dose_number' => $this->faker->randomElement([1, 1, 1, 2, 2, 3]),
            'administered_at' => $this->faker->dateTimeBetween('-15 years', 'now')->format('Y-m-d'),
            'next_due_at' => $this->faker->boolean(40) ? $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d') : null,
            'provider' => $this->faker->boolean(50) ? $this->faker->randomElement([
                'Dispensaire de quartier',
                'Cabinet médical',
                'Centre de vaccination',
            ]) : null,
            'batch_number' => $this->faker->boolean(50) ? strtoupper($this->faker->bothify('???###')) : null,
            'notes' => $this->faker->boolean(25) ? $this->faker->sentence(8) : null,
        ];
    }
}
