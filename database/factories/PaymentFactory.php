<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(3, 10, 500);

        return [
            'payment_number' => 'PAY-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'invoice_id' => Invoice::factory(),
            'patient_id' => fn (array $attributes): int => (int) Invoice::find($attributes['invoice_id'])?->patient_id,
            'payment_method_id' => PaymentMethod::factory(),
            'payment_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'amount' => $amount,
            'status' => PaymentStatus::Completed,
            'reference' => $this->faker->boolean(30) ? $this->faker->bothify('??-#####') : null,
            'notes' => $this->faker->boolean(20) ? $this->faker->sentence(4) : null,
            'received_by' => null,
            'cancelled_at' => null,
            'cancelled_reason' => null,
        ];
    }
}
