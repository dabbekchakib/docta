<?php

namespace Database\Factories;

use App\Enums\ResultAbnormality;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryResult>
 */
class LaboratoryResultFactory extends Factory
{
    protected $model = LaboratoryResult::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $numeric = $this->faker->randomFloat(1, 0.5, 200);

        return [
            'laboratory_request_item_id' => LaboratoryRequestItem::factory(),
            'parameter_name' => $this->faker->words(2, true),
            'value' => (string) $numeric,
            'numeric_value' => $numeric,
            'unit' => $this->faker->randomElement(['g/L', 'mg/dL', 'mmol/L', 'UI/L']),
            'reference_min' => $this->faker->randomFloat(1, 0.1, 10),
            'reference_max' => $this->faker->randomFloat(1, 50, 250),
            'reference_text' => null,
            'abnormality' => $this->faker->randomElement(ResultAbnormality::cases()),
            'comment' => $this->faker->boolean(25) ? $this->faker->sentence(6) : null,
            'resulted_at' => now(),
            'validated_at' => null,
            'validated_by' => null,
        ];
    }
}
