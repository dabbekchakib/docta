<?php

namespace Database\Factories;

use App\Enums\SampleType;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryRequestItem>
 */
class LaboratoryRequestItemFactory extends Factory
{
    protected $model = LaboratoryRequestItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'laboratory_request_id' => LaboratoryRequest::factory(),
            'laboratory_test_id' => LaboratoryTest::factory(),
            'status' => 'pending',
            'sample_type' => $this->faker->randomElement(SampleType::cases()),
            'instructions' => $this->faker->boolean(30) ? 'À jeun' : null,
            'notes' => $this->faker->boolean(20) ? $this->faker->sentence(5) : null,
            'sort_order' => 1,
        ];
    }
}
