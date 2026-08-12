<?php

namespace Database\Factories;

use App\Enums\SampleType;
use App\Models\LaboratoryTest;
use App\Models\TestCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryTest>
 */
class LaboratoryTestFactory extends Factory
{
    protected $model = LaboratoryTest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_category_id' => TestCategory::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'code' => strtoupper($this->faker->lexify('TEST-????')),
            'description' => $this->faker->boolean(40) ? $this->faker->sentence(8) : null,
            'sample_type' => $this->faker->randomElement(SampleType::cases()),
            'unit' => $this->faker->boolean(70) ? $this->faker->randomElement(['g/L', 'mg/dL', 'mmol/L', 'UI/L', 'µg/L']) : null,
            'default_reference_value' => $this->faker->boolean(40) ? $this->faker->numerify('##–##') : null,
            'is_active' => true,
            'requires_fasting' => $this->faker->boolean(20),
            'instructions' => $this->faker->boolean(30) ? $this->faker->sentence(10) : null,
        ];
    }
}
