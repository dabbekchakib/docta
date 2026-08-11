<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\SurgicalHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurgicalHistory>
 */
class SurgicalHistoryFactory extends Factory
{
    protected $model = SurgicalHistory::class;

    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'procedure_name' => $this->faker->randomElement([
                'Appendicectomie',
                'Cholécystectomie',
                'Césarienne',
                'Réduction de fracture',
                'Ablation de kyste',
                'Hernioplastie',
            ]),
            'hospital' => $this->faker->randomElement([
                'Hôpital Charles Nicolle',
                'Hôpital La Rabta',
                'Clinique El Manar',
                'Hôpital Habib Thameur',
            ]),
            'surgeon' => $this->faker->boolean(60) ? 'Dr '.$this->faker->lastName : null,
            'performed_at' => $this->faker->dateTimeBetween('-15 years', '-6 months')->format('Y-m-d'),
            'reason' => $this->faker->boolean(50) ? $this->faker->sentence(8) : null,
            'complications' => $this->faker->boolean(15) ? $this->faker->sentence(8) : null,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence(8) : null,
        ];
    }
}
