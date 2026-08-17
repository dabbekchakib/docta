<?php

namespace App\Services\AI\Tools;

use App\Models\Patient;
use App\Models\User;

class ViewPatientTool extends BaseAITool
{
    public function getName(): string
    {
        return 'view_patient';
    }

    public function getDescription(): string
    {
        return 'Consulte la fiche détaillée d\'un patient par son ID ou numéro. Inclut les informations personnelles et les allergies critiques.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'patient_id' => [
                    'type' => 'integer',
                    'description' => 'ID du patient',
                ],
                'patient_number' => [
                    'type' => 'string',
                    'description' => 'Numéro du patient (ex: PAT-000001)',
                ],
            ],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['patients.view'];
    }

    public function execute(User $user, array $parameters): array
    {
        $query = Patient::query();

        if (! empty($parameters['patient_id'])) {
            $query->where('id', $parameters['patient_id']);
        } elseif (! empty($parameters['patient_number'])) {
            $query->where('patient_number', $parameters['patient_number']);
        } else {
            return $this->error('Veuillez fournir un ID ou un numéro de patient.');
        }

        $patient = $query->with(['medicalRecord.allergies', 'medicalRecord.chronicDiseases'])->first();

        if (! $patient) {
            return $this->error('Patient non trouvé.');
        }

        $data = [
            'id' => $patient->id,
            'patient_number' => $patient->patient_number,
            'full_name' => trim("{$patient->first_name} {$patient->last_name}"),
            'gender' => $patient->gender?->label(),
            'birth_date' => $patient->birth_date?->format('d/m/Y'),
            'phone' => $patient->phone,
            'email' => $patient->email,
            'address' => $patient->address,
            'city' => $patient->city,
            'blood_group' => $patient->blood_group,
            'has_cnam' => $patient->has_cnam,
            'status' => $patient->status?->value,
        ];

        // Allergies critiques
        if ($patient->medicalRecord) {
            $criticalAllergies = $patient->medicalRecord->criticalAllergies();
            if ($criticalAllergies->isNotEmpty()) {
                $data['critical_allergies'] = $criticalAllergies->pluck('allergen')->toArray();
            }

            $activeDiseases = $patient->medicalRecord->activeChronicDiseases();
            if ($activeDiseases->isNotEmpty()) {
                $data['chronic_diseases'] = $activeDiseases->pluck('disease_name')->toArray();
            }
        }

        // Nombre de consultations et rendez-vous
        $data['consultations_count'] = $patient->consultations()->count();
        $data['appointments_count'] = $patient->appointments()->count();
        $data['invoices_count'] = $patient->invoices()->count();

        $this->logActivity(
            $user,
            null,
            "Consultation fiche patient: {$patient->patient_number}",
            null,
            $parameters,
            'success',
            "Patient {$patient->patient_number} consulté",
        );

        return $this->success($data, "Fiche patient {$patient->patient_number} - ".trim("{$patient->first_name} {$patient->last_name}"));
    }
}
