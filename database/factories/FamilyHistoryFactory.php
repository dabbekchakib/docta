<?php

namespace Database\Factories;

use App\Enums\MedicalHistoryStatus;
use App\Enums\RelativeType;
use App\Models\FamilyHistory;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyHistory>
 */
class FamilyHistoryFactory extends Factory
{
    protected $model = FamilyHistory::class;

    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'relative' => $this->faker->randomElement(RelativeType::cases()),
            'condition' => $this->faker->randomElement([
                'Hypertension artérielle',
                'Diabète de type 2',
                'Cancer du sein',
                'Maladies cardiovasculaires',
                'Asthme',
                'Diabète de type 1',
            ]),
            'description' => $this->faker->boolean(60) ? $this->faker->sentence(10) : null,
            'diagnosed_at' => $this->faker->boolean(50) ? $this->faker->dateTimeBetween('-30 years', '-1 year')->format('Y-m-d') : null,
            'status' => $this->faker->randomElement([
                MedicalHistoryStatus::Active,
                MedicalHistoryStatus::Active,
                MedicalHistoryStatus::Unknown,
            ]),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence(8) : null,
        ];
    }
}
