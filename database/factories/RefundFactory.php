<?php

namespace Database\Factories;

use App\Enums\RefundStatus;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refund>
 */
class RefundFactory extends Factory
{
    protected $model = Refund::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'refund_number' => 'REM-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'payment_id' => Payment::factory(),
            'credit_note_id' => null,
            'patient_id' => fn (array $attributes): int => (int) Payment::find($attributes['payment_id'])?->patient_id,
            'refund_date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'amount' => $this->faker->randomFloat(3, 10, 200),
            'reason' => $this->faker->sentence(5),
            'status' => RefundStatus::Completed,
            'refund_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'check']),
            'reference' => $this->faker->boolean(30) ? $this->faker->bothify('??-#####') : null,
            'requested_at' => now()->subDay(),
            'approved_at' => now()->subHours(6),
            'completed_at' => now(),
            'rejected_at' => null,
            'rejected_reason' => null,
            'cancelled_at' => null,
            'approved_by' => null,
            'created_by' => null,
        ];
    }
}
