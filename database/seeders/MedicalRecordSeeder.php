<?php

namespace Database\Seeders;

use App\Enums\AllergyStatus;
use App\Enums\ChronicDiseaseStatus;
use App\Enums\MedicationStatus;
use App\Enums\SmokingStatus;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Services\MedicalRecordService;
use Illuminate\Database\Seeder;

class MedicalRecordSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::query()->orderBy('id')->get();

        if ($patients->isEmpty()) {
            $patients = Patient::factory()->count(60)->create();
        }

        $service = app(MedicalRecordService::class);

        $records = $patients->map(fn (Patient $patient): MedicalRecord => $service->ensureForPatient($patient));

        foreach ($records as $record) {
            $this->seedAllergies($record);
            $this->seedChronicDiseases($record);
            $this->seedHistories($record);
            $this->seedMedications($record);
            $this->seedVaccinations($record);
            $this->seedLifestyle($record);
        }
    }

    private function seedAllergies(MedicalRecord $record): void
    {
        if (rand(1, 10) > 7) {
            return;
        }

        $count = rand(1, 3);

        for ($i = 0; $i < $count; $i++) {
            $record->allergies()->create(
                \App\Models\Allergy::factory()
                    ->make(['medical_record_id' => $record->id])
                    ->only(['allergen', 'type', 'reaction', 'severity', 'discovered_at', 'status', 'notes'])
            );
        }

        // Une allergie critique sur ~15 % des dossiers pour les alertes.
        if (rand(1, 100) <= 15) {
            $record->allergies()->create([
                'allergen' => collect(['Pénicilline', 'Arachides', 'Amoxicilline', 'Iode'])->random(),
                'type' => 'medicament',
                'reaction' => 'Choc anaphylactique',
                'severity' => 'critical',
                'discovered_at' => now()->subYears(rand(1, 8))->format('Y-m-d'),
                'status' => AllergyStatus::Active->value,
            ]);
        }
    }

    private function seedChronicDiseases(MedicalRecord $record): void
    {
        if (rand(1, 10) > 6) {
            return;
        }

        $diseases = [
            ['disease_name' => 'Hypertension artérielle', 'icd_code' => 'I10'],
            ['disease_name' => 'Diabète de type 2', 'icd_code' => 'E11'],
            ['disease_name' => 'Asthme', 'icd_code' => 'J45'],
            ['disease_name' => 'Hypercholestérolémie', 'icd_code' => 'E78'],
            ['disease_name' => 'Hypothyroïdie', 'icd_code' => 'E03'],
            ['disease_name' => 'Insuffisance rénale chronique', 'icd_code' => 'N18'],
        ];

        foreach (collect($diseases)->random(rand(1, 2)) as $disease) {
            $record->chronicDiseases()->create([
                ...$disease,
                'diagnosed_at' => now()->subYears(rand(1, 15))->format('Y-m-d'),
                'status' => collect([
                    ChronicDiseaseStatus::Active->value,
                    ChronicDiseaseStatus::Controlled->value,
                    ChronicDiseaseStatus::Controlled->value,
                ])->random(),
                'severity' => collect(['mild', 'moderate', 'moderate', 'severe'])->random(),
                'treatment' => rand(0, 1) ? 'Traitement médical adapté' : null,
            ]);
        }
    }

    private function seedHistories(MedicalRecord $record): void
    {
        if (rand(1, 10) > 7) {
            return;
        }

        $types = ['maladie', 'infection', 'traumatisme', 'hospitalisation', 'autre'];

        for ($i = 0; $i < rand(1, 3); $i++) {
            $record->medicalHistories()->create([
                'type' => collect($types)->random(),
                'title' => collect([
                    'Chirurgie abdominale',
                    'Infection urinaire',
                    'Fracture du poignet',
                    'Appendicectomie',
                    'Pneumonie',
                    'Migraine chronique',
                ])->random(),
                'description' => rand(0, 1) ? fake()->sentence(10) : null,
                'diagnosed_at' => now()->subYears(rand(1, 20))->format('Y-m-d'),
                'resolved_at' => rand(0, 1) ? now()->subYears(rand(0, 5))->format('Y-m-d') : null,
                'status' => collect(['active', 'resolved', 'resolved', 'unknown'])->random(),
                'created_by' => null,
            ]);
        }

        if (rand(1, 10) <= 3) {
            $record->surgicalHistories()->create([
                'procedure_name' => collect(['Cholécystectomie', 'Hernie inguinale', 'Césarienne', 'Appendicectomie'])->random(),
                'hospital' => fake()->company(),
                'surgeon' => rand(0, 1) ? 'Dr '.fake()->name() : null,
                'performed_at' => now()->subYears(rand(1, 15))->format('Y-m-d'),
                'reason' => rand(0, 1) ? fake()->sentence(6) : null,
            ]);
        }

        if (rand(1, 10) <= 4) {
            $record->familyHistories()->create([
                'relative' => collect(['pere', 'mere', 'frere', 'soeur', 'grand_parent'])->random(),
                'condition' => collect([
                    'Diabète',
                    'Hypertension',
                    'Cardiopathie',
                    'Cancer du sein',
                    'Asthme',
                ])->random(),
                'description' => rand(0, 1) ? fake()->sentence(8) : null,
                'diagnosed_at' => now()->subYears(rand(1, 30))->format('Y-m-d'),
                'status' => 'unknown',
            ]);
        }
    }

    private function seedMedications(MedicalRecord $record): void
    {
        if (rand(1, 10) > 6) {
            return;
        }

        $medications = [
            ['name' => 'Amlodipine', 'active_ingredient' => 'Amlodipine', 'dosage' => '5 mg', 'frequency' => '1 comprimé le matin', 'route' => 'orale'],
            ['name' => 'Metformine', 'active_ingredient' => 'Metformine', 'dosage' => '850 mg', 'frequency' => '1 comprimé matin et soir', 'route' => 'orale'],
            ['name' => 'Ramipril', 'active_ingredient' => 'Ramipril', 'dosage' => '5 mg', 'frequency' => '1 comprimé le matin', 'route' => 'orale'],
            ['name' => 'Atorvastatine', 'active_ingredient' => 'Atorvastatine', 'dosage' => '20 mg', 'frequency' => '1 comprimé le soir', 'route' => 'orale'],
            ['name' => 'Levothyrox', 'active_ingredient' => 'Lévothyroxine', 'dosage' => '75 µg', 'frequency' => '1 comprimé à jeun', 'route' => 'orale'],
            ['name' => 'Ventoline', 'active_ingredient' => 'Salbutamol', 'dosage' => '100 µg/dose', 'frequency' => 'À la demande', 'route' => 'inhalation'],
        ];

        foreach (collect($medications)->random(rand(1, 3)) as $medication) {
            $stopped = rand(1, 10) <= 3;

            $record->medications()->create([
                ...$medication,
                'started_at' => now()->subMonths(rand(1, 24))->format('Y-m-d'),
                'ended_at' => $stopped ? now()->subDays(rand(1, 180))->format('Y-m-d') : null,
                'prescriber' => rand(0, 1) ? 'Dr '.fake()->name() : null,
                'status' => $stopped ? MedicationStatus::Stopped->value : MedicationStatus::Active->value,
            ]);
        }
    }

    private function seedVaccinations(MedicalRecord $record): void
    {
        if (rand(1, 10) > 5) {
            return;
        }

        $vaccines = [
            ['vaccine_name' => 'Grippe saisonnière', 'dose_number' => 1],
            ['vaccine_name' => 'Hépatite B', 'dose_number' => 3],
            ['vaccine_name' => 'Tétanos-Diphtérie', 'dose_number' => null],
            ['vaccine_name' => 'COVID-19', 'dose_number' => 2],
        ];

        foreach (collect($vaccines)->random(rand(1, 3)) as $vaccine) {
            $record->vaccinations()->create([
                ...$vaccine,
                'administered_at' => now()->subMonths(rand(1, 36))->format('Y-m-d'),
                'next_due_at' => rand(0, 1) ? now()->addMonths(rand(1, 12))->format('Y-m-d') : null,
                'provider' => rand(0, 1) ? fake()->company() : null,
                'batch_number' => rand(0, 1) ? strtoupper(fake()->bothify('##??####')) : null,
            ]);
        }
    }

    private function seedLifestyle(MedicalRecord $record): void
    {
        if ($record->lifestyle) {
            return;
        }

        $record->lifestyle()->create([
            'smoking_status' => collect([SmokingStatus::Never->value, SmokingStatus::Never->value, SmokingStatus::Former->value, SmokingStatus::Current->value])->random(),
            'smoking_quantity' => rand(0, 1) ? (rand(5, 40).' paquets/an') : null,
            'alcohol_status' => collect(['never', 'occasional', 'regular', 'former'])->random(),
            'physical_activity' => rand(0, 1) ? collect(['2 à 3 fois par semaine', 'Aucune', 'Quotidienne'])->random() : null,
            'diet' => rand(0, 1) ? collect(['Équilibrée', 'Riche en sel', 'Végétarienne'])->random() : null,
            'sleep_quality' => rand(0, 1) ? collect(['bonne', 'moyenne', 'mauvaise'])->random() : null,
            'occupation_risk' => rand(0, 1) ? fake()->jobTitle() : null,
        ]);
    }
}
