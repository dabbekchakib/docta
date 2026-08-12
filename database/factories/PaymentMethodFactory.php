<?php

namespace Database\Factories;

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(PaymentMethodType::cases());

        return [
            'name' => $this->faker->word,
            'code' => strtoupper($this->faker->unique()->bothify('???')),
            'type' => $type,
            'description' => $this->faker->boolean(40) ? $this->faker->sentence(5) : null,
            'is_active' => true,
        ];
    }
}
