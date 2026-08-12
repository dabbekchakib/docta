<?php

namespace Database\Factories;

use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaxRate>
 */
class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rate = $this->faker->randomElement([0, 7, 13, 19]);

        return [
            'name' => 'TVA '.$rate.' %',
            'code' => 'TVA'.$rate,
            'rate' => $rate,
            'is_active' => true,
        ];
    }
}
