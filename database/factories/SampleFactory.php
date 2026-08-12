<?php

namespace Database\Factories;

use App\Enums\SampleType;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\Sample;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sample>
 */
class SampleFactory extends Factory
{
    protected $model = Sample::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'laboratory_request_id' => LaboratoryRequest::factory(),
            'laboratory_request_item_id' => null,
            'sample_number' => 'ECH-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'sample_type' => $this->faker->randomElement(SampleType::cases()),
            'collected_at' => now(),
            'collected_by' => null,
            'received_at' => null,
            'status' => $this->faker->randomElement(['pending', 'collected', 'received', 'processed']),
            'rejection_reason' => null,
            'notes' => $this->faker->boolean(20) ? $this->faker->sentence(5) : null,
        ];
    }

    public function collected(): static
    {
        return $this->state(fn (): array => ['status' => 'collected', 'collected_at' => now()]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => 'rejected',
            'rejection_reason' => 'Échantillon insuffisant',
        ]);
    }
}
