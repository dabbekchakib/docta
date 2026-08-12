<?php

namespace Database\Factories;

use App\Enums\ServiceCategory;
use App\Models\Service;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = $this->faker->randomElement(ServiceCategory::cases());

        return [
            'code' => strtoupper($this->faker->unique()->bothify('???###')),
            'name' => $this->faker->words(3, true),
            'category' => $category,
            'price' => $this->faker->randomFloat(3, 10, 500),
            'description' => $this->faker->boolean(40) ? $this->faker->sentence(6) : null,
            'tax_rate_id' => TaxRate::factory(),
            'is_active' => true,
        ];
    }
}
