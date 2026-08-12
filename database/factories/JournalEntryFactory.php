<?php

namespace Database\Factories;

use App\Enums\JournalEntryStatus;
use App\Enums\JournalEntryType;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_number' => 'ECR-'.now()->format('Y').'-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'entry_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'type' => JournalEntryType::Manual,
            'description' => $this->faker->sentence(4),
            'source_type' => null,
            'source_id' => null,
            'status' => JournalEntryStatus::Posted,
            'posted_at' => now(),
            'cancelled_at' => null,
            'cancelled_reason' => null,
            'created_by' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => JournalEntryStatus::Draft,
            'posted_at' => null,
        ]);
    }
}
