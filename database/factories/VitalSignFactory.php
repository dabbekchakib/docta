<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\VitalSign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VitalSign>
 */
class VitalSignFactory extends Factory
{
    protected $model = VitalSign::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $height = $this->faker->numberBetween(150, 190);
        $weight = $this->faker->numberBetween(50, 110);

        return [
            'consultation_id' => Consultation::factory(),
            'temperature' => $this->faker->randomFloat(1, 36, 39.5),
            'weight' => $weight,
            'height' => $height,
            'blood_pressure' => $this->faker->randomElement(['110/70', '120/80', '130/85', '140/90', '150/95']),
            'heart_rate' => $this->faker->numberBetween(55, 110),
            'oxygen_saturation' => $this->faker->numberBetween(94, 100),
            'respiratory_rate' => $this->faker->numberBetween(12, 22),
        ];
    }
}
