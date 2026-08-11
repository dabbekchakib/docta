<?php

namespace Database\Factories;

use App\Enums\MedicalDocumentType;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalDocument>
 */
class MedicalDocumentFactory extends Factory
{
    protected $model = MedicalDocument::class;

    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'title' => $this->faker->randomElement([
                'Bilan sanguin complet',
                'Radiographie thoracique',
                'Compte rendu de consultation',
                'Certificat d\'aptitude',
                'Ordonnance de renouvellement',
                'Compte rendu opératoire',
            ]),
            'document_type' => $this->faker->randomElement([
                MedicalDocumentType::Analysis,
                MedicalDocumentType::Radiology,
                MedicalDocumentType::Report,
                MedicalDocumentType::Certificate,
                MedicalDocumentType::Prescription,
                MedicalDocumentType::Hospitalisation,
                MedicalDocumentType::Other,
            ]),
            'description' => $this->faker->boolean(60) ? $this->faker->sentence(10) : null,
            'document_date' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'issued_by' => $this->faker->boolean(60) ? 'Dr '.$this->faker->lastName : null,
            'is_confidential' => $this->faker->boolean(15),
            'created_by' => null,
        ];
    }
}
