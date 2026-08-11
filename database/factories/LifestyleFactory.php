<?php

namespace Database\Factories;

use App\Enums\AlcoholStatus;
use App\Enums\SmokingStatus;
use App\Models\Lifestyle;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lifestyle>
 */
class LifestyleFactory extends Factory
{
    protected $model = Lifestyle::class;

    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'smoking_status' => $this->faker->randomElement([
                SmokingStatus::Never,
                SmokingStatus::Never,
                SmokingStatus::Former,
                SmokingStatus::Current,
                SmokingStatus::Unknown,
            ]),
            'smoking_quantity' => $this->faker->boolean(30) ? $this->faker->randomElement([
                '1-5 cigarettes/jour',
                '6-10 cigarettes/jour',
                '11-20 cigarettes/jour',
                'Plus de 20 cigarettes/jour',
            ]) : null,
            'alcohol_status' => $this->faker->randomElement(AlcoholStatus::cases()),
            'physical_activity' => $this->faker->boolean(60) ? $this->faker->randomElement([
                'Sédentaire',
                'Activité légère 1-2x/semaine',
                'Activité modérée 3x/semaine',
                'Sport intense régulier',
            ]) : null,
            'diet' => $this->faker->boolean(50) ? $this->faker->randomElement([
                'Régime équilibré',
                'Régime pauvre en sel',
                'Régime pauvre en sucre',
                'Végétarien',
                'Sans restriction',
            ]) : null,
            'sleep_quality' => $this->faker->boolean(50) ? $this->faker->randomElement([
                'Bonne',
                'Moyenne',
                'Mauvaise',
                'Troubles du sommeil',
            ]) : null,
            'occupation_risk' => $this->faker->boolean(25) ? $this->faker->randomElement([
                'Exposition chimique',
                'Travail physique lourd',
                'Travail de nuit',
                'Exposition au bruit',
            ]) : null,
            'other_risks' => $this->faker->boolean(20) ? $this->faker->sentence(8) : null,
            'notes' => $this->faker->boolean(25) ? $this->faker->sentence(8) : null,
        ];
    }
}
