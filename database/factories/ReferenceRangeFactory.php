<?php

namespace Database\Factories;

use App\Models\LaboratoryTest;
use App\Models\ReferenceRange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferenceRange>
 */
class ReferenceRangeFactory extends Factory
{
    protected $model = ReferenceRange::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = $this->faker->randomFloat(1, 0.1, 50);

        return [
            'laboratory_test_id' => LaboratoryTest::factory(),
            'gender' => $this->faker->randomElement(['male', 'female', 'all']),
            'age_min' => $this->faker->optional(0.5)->numberBetween(0, 18),
            'age_max' => $this->faker->optional(0.5)->numberBetween(19, 100),
            'min_value' => $min,
            'max_value' => $this->faker->randomFloat(1, $min + 1, $min + 200),
            'unit' => $this->faker->optional(0.7)->randomElement(['g/L', 'mg/dL', 'mmol/L']),
            'reference_text' => $this->faker->optional(0.3)->sentence(4),
        ];
    }
}
