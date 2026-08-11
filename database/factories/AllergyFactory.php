<?php

namespace Database\Factories;

use App\Enums\AllergySeverity;
use App\Enums\AllergyStatus;
use App\Enums\AllergyType;
use App\Models\Allergy;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Allergy>
 */
class AllergyFactory extends Factory
{
    protected $model = Allergy::class;

    public function definition(): array
    {
        $severity = $this->faker->randomElement([
            AllergySeverity::Mild, AllergySeverity::Mild, AllergySeverity::Mild,
            AllergySeverity::Moderate, AllergySeverity::Moderate,
            AllergySeverity::Severe,
        ]);

        $type = $this->faker->randomElement([
            AllergyType::Medication, AllergyType::Medication,
            AllergyType::Food, AllergyType::Pollen,
            AllergyType::Latex, AllergyType::Animal, AllergyType::Other,
        ]);

        return [
            'medical_record_id' => MedicalRecord::factory(),
            'allergen' => $this->faker->randomElement([
                'Pénicilline',
                'Aspirine',
                'Amoxicilline',
                'Sulfamides',
                'Arachides',
                'Fruits de mer',
                'Lait',
                'Pollen',
                'Latex',
                'Poils de chat',
            ]),
            'type' => $type,
            'reaction' => $this->faker->boolean(70) ? $this->faker->randomElement([
                'Urticaire',
                'Œdème de Quincke',
                'Choc anaphylactique',
                'Éruption cutanée',
                'Difficultés respiratoires',
                'Démangeaisons',
            ]) : null,
            'severity' => $severity,
            'discovered_at' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'status' => $this->faker->randomElement([
                AllergyStatus::Active,
                AllergyStatus::Active,
                AllergyStatus::Active,
                AllergyStatus::Inactive,
            ]),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence(8) : null,
        ];
    }

    /**
     * Allergie active de sévérité critique.
     */
    public function critical(): static
    {
        return $this->state(fn (): array => [
            'severity' => AllergySeverity::Critical,
            'status' => AllergyStatus::Active,
        ]);
    }
}
