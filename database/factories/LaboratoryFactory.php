<?php

namespace Database\Factories;

use App\Models\Laboratory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Laboratory>
 */
class LaboratoryFactory extends Factory
{
    protected $model = Laboratory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Laboratoire '.$this->faker->city,
            'code' => strtoupper($this->faker->lexify('LAB-????')),
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'contact_name' => $this->faker->name,
            'is_active' => $this->faker->boolean(90),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence(6) : null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }
}
