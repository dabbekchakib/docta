<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receipt>
 */
class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'receipt_number' => 'REC-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'payment_id' => Payment::factory(),
            'invoice_id' => fn (array $attributes): int => (int) Payment::find($attributes['payment_id'])?->invoice_id,
            'patient_id' => fn (array $attributes): int => (int) Payment::find($attributes['payment_id'])?->patient_id,
            'receipt_date' => now()->toDateString(),
            'amount' => fn (array $attributes): float => (float) Payment::find($attributes['payment_id'])?->amount,
            'notes' => null,
            'created_by' => null,
        ];
    }
}
