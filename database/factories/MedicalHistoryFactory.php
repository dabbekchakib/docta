<?php

namespace Database\Factories;

use App\Enums\MedicalHistoryStatus;
use App\Enums\MedicalHistoryType;
use App\Models\MedicalHistory;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalHistory>
 */
class MedicalHistoryFactory extends Factory
{
    protected $model = MedicalHistory::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement([
            MedicalHistoryStatus::Active,
            MedicalHistoryStatus::Active,
            MedicalHistoryStatus::Resolved,
            MedicalHistoryStatus::Unknown,
        ]);

        return [
            'medical_record_id' => MedicalRecord::factory(),
            'type' => $this->faker->randomElement(MedicalHistoryType::cases()),
            'title' => $this->faker->randomElement([
                'Hypertension artérielle',
                'Asthme',
                'Tuberculose',
                'Fracture du radius',
                'Appendicectomie',
                'Pneumonie',
                'Diabète gestationnel',
                'Hépatite B',
            ]),
            'description' => $this->faker->boolean(70) ? $this->faker->sentence(12) : null,
            'diagnosed_at' => $this->faker->dateTimeBetween('-20 years', '-1 year')->format('Y-m-d'),
            'resolved_at' => $status === MedicalHistoryStatus::Resolved
                ? $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d')
                : null,
            'status' => $status,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence(8) : null,
            'created_by' => null,
        ];
    }
}
