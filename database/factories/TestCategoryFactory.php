<?php

namespace Database\Factories;

use App\Models\TestCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TestCategory>
 */
class TestCategoryFactory extends Factory
{
    protected $model = TestCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'code' => strtoupper($this->faker->lexify('CAT-????')),
            'description' => $this->faker->boolean(40) ? $this->faker->sentence(8) : null,
            'is_active' => true,
        ];
    }
}
