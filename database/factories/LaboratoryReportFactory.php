<?php

namespace Database\Factories;

use App\Models\LaboratoryReport;
use App\Models\LaboratoryRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaboratoryReport>
 */
class LaboratoryReportFactory extends Factory
{
    protected $model = LaboratoryReport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'laboratory_request_id' => LaboratoryRequest::factory(),
            'report_number' => 'CR-LAB-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'report_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'summary' => $this->faker->boolean(60) ? $this->faker->sentence(8) : null,
            'comments' => $this->faker->boolean(30) ? $this->faker->sentence(8) : null,
            'validated_at' => now(),
            'validated_by' => null,
        ];
    }
}
