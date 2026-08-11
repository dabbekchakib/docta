<?php

namespace Database\Factories;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrescriptionItem>
 */
class PrescriptionItemFactory extends Factory
{
    protected $model = PrescriptionItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $medicines = [
            ['medicine_name' => 'Paracétamol', 'active_ingredient' => 'Paracétamol', 'dosage' => '500 mg', 'form' => 'tablet', 'route' => 'orale'],
            ['medicine_name' => 'Amoxicilline', 'active_ingredient' => 'Amoxicilline', 'dosage' => '1 g', 'form' => 'tablet', 'route' => 'orale'],
            ['medicine_name' => 'Ibuprofène', 'active_ingredient' => 'Ibuprofène', 'dosage' => '400 mg', 'form' => 'tablet', 'route' => 'orale'],
            ['medicine_name' => 'Ventoline', 'active_ingredient' => 'Salbutamol', 'dosage' => '100 µg/dose', 'form' => 'inhalation', 'route' => 'inhalation'],
            ['medicine_name' => 'Oméprazole', 'active_ingredient' => 'Oméprazole', 'dosage' => '20 mg', 'form' => 'capsule', 'route' => 'orale'],
            ['medicine_name' => 'Amoxiclav', 'active_ingredient' => 'Amoxicilline + acide clavulanique', 'dosage' => '1 g/125 mg', 'form' => 'tablet', 'route' => 'orale'],
        ];

        $medicine = $this->faker->randomElement($medicines);

        return [
            'prescription_id' => Prescription::factory(),
            'medicine_name' => $medicine['medicine_name'],
            'active_ingredient' => $medicine['active_ingredient'],
            'dosage' => $medicine['dosage'],
            'form' => $medicine['form'],
            'route' => $medicine['route'],
            'frequency' => $this->faker->randomElement(['3 fois par jour', '2 fois par jour', '1 fois par jour', 'Le soir', 'Le matin', 'À la demande']),
            'duration' => (string) $this->faker->numberBetween(3, 30),
            'duration_unit' => $this->faker->randomElement(['jour', 'jour', 'jour', 'semaine', 'mois']),
            'quantity' => (string) $this->faker->numberBetween(1, 60),
            'instructions' => $this->faker->boolean(60) ? 'À prendre avec un grand verre d\'eau' : null,
            'notes' => null,
            'sort_order' => 0,
        ];
    }
}
