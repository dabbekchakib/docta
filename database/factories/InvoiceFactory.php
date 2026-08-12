<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = $this->faker->randomFloat(3, 20, 800);

        return [
            'invoice_number' => 'FAC-'.now()->format('Y').'-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'consultation_id' => null,
            'appointment_id' => null,
            'laboratory_request_id' => null,
            'invoice_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'due_date' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d') : null,
            'status' => InvoiceStatus::Issued,
            'discount_type' => 'none',
            'discount_value' => 0,
            'subtotal' => $total,
            'discount_amount' => 0,
            'taxable_base' => $total,
            'tax_amount' => 0,
            'total' => $total,
            'amount_paid' => 0,
            'amount_remaining' => $total,
            'currency' => 'TND',
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence(6) : null,
            'issued_at' => now(),
            'cancelled_at' => null,
            'cancelled_reason' => null,
            'created_by' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'status' => InvoiceStatus::Paid,
            'amount_paid' => $this->faker->randomFloat(3, 20, 800),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => InvoiceStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_reason' => 'Annulée',
        ]);
    }
}
