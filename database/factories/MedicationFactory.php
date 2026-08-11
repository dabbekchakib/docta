<?php

namespace Database\Factories;

use App\Enums\MedicationStatus;
use App\Models\MedicalRecord;
use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medication>
 */
class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    public function definition(): array
    {
        $medication = $this->faker->randomElement([
            ['Metformine', 'Metformine', '850 mg', '2x/jour'],
            ['Amlodipine', 'Amlodipine', '5 mg', '1x/jour'],
            ['Simvastatine', 'Simvastatine', '20 mg', '1x/jour le soir'],
            ['Ventoline', 'Salbutamol', '100 µg', 'À la demande'],
            ['Lévothyrox', 'Lévothyroxine', '50 µg', '1x/jour le matin'],
            ['Amlor', 'Amlodipine', '10 mg', '1x/jour'],
            ['Oméprazole', 'Oméprazole', '20 mg', '1x/jour avant repas'],
        ]);

        return [
            'medical_record_id' => MedicalRecord::factory(),
            'name' => $medication[0],
            'active_ingredient' => $medication[1],
            'dosage' => $medication[2],
            'frequency' => $medication[3],
            'route' => $this->faker->randomElement(['orale', 'inhalée', 'intraveineuse', 'topique']),
            'started_at' => $this->faker->dateTimeBetween('-5 years', '-1 month')->format('Y-m-d'),
            'ended_at' => null,
            'prescriber' => $this->faker->boolean(70) ? 'Dr '.$this->faker->lastName : null,
            'status' => $this->faker->randomElement([
                MedicationStatus::Active,
                MedicationStatus::Active,
                MedicationStatus::Stopped,
                MedicationStatus::Unknown,
            ]),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence(8) : null,
        ];
    }
}
