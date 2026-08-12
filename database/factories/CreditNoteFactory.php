<?php

namespace Database\Factories;

use App\Enums\CreditNoteStatus;
use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditNote>
 */
class CreditNoteFactory extends Factory
{
    protected $model = CreditNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'credit_note_number' => 'AV-'.now()->format('Y').'-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'invoice_id' => Invoice::factory(),
            'patient_id' => fn (array $attributes): int => (int) Invoice::find($attributes['invoice_id'])?->patient_id,
            'credit_note_date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'amount' => $this->faker->randomFloat(3, 10, 300),
            'reason' => $this->faker->sentence(5),
            'status' => CreditNoteStatus::Issued,
            'issued_at' => now(),
            'cancelled_at' => null,
            'cancelled_reason' => null,
            'created_by' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => CreditNoteStatus::Draft,
            'issued_at' => null,
        ]);
    }
}
