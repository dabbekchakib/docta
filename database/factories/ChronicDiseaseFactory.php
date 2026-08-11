<?php

namespace Database\Factories;

use App\Enums\AllergySeverity;
use App\Enums\ChronicDiseaseStatus;
use App\Models\ChronicDisease;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChronicDisease>
 */
class ChronicDiseaseFactory extends Factory
{
    protected $model = ChronicDisease::class;

    public function definition(): array
    {
        $disease = $this->faker->randomElement([
            ['Diabète de type 2', 'E11'],
            ['Hypertension artérielle', 'I10'],
            ['Asthme', 'J45'],
            ['Insuffisance cardiaque', 'I50'],
            ['Maladie rénale chronique', 'N18'],
            ['Épilepsie', 'G40'],
            ['Hypothyroïdie', 'E03'],
            ['Hypercholestérolémie', 'E78'],
        ]);

        return [
            'medical_record_id' => MedicalRecord::factory(),
            'disease_name' => $disease[0],
            'icd_code' => $disease[1],
            'diagnosed_at' => $this->faker->dateTimeBetween('-20 years', '-6 months')->format('Y-m-d'),
            'status' => $this->faker->randomElement([
                ChronicDiseaseStatus::Active,
                ChronicDiseaseStatus::Active,
                ChronicDiseaseStatus::Controlled,
                ChronicDiseaseStatus::Unknown,
            ]),
            'severity' => $this->faker->randomElement([
                AllergySeverity::Mild,
                AllergySeverity::Moderate,
                AllergySeverity::Moderate,
                AllergySeverity::Severe,
            ]),
            'treatment' => $this->faker->boolean(70) ? $this->faker->sentence(8) : null,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence(8) : null,
        ];
    }
}
