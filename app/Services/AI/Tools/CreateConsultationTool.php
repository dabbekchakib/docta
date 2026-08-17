<?php

namespace App\Services\AI\Tools;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;

class CreateConsultationTool extends BaseAITool
{
    public function getName(): string
    {
        return 'create_consultation';
    }

    public function getDescription(): string
    {
        return 'Propose de créer une consultation médicale pour un patient. Nécessite confirmation. L\'IA ne suggère jamais de diagnostic, elle crée uniquement la structure.';
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
                'doctor_id' => [
                    'type' => 'integer',
                    'description' => 'ID du médecin',
                ],
                'appointment_id' => [
                    'type' => 'integer',
                    'description' => 'ID du rendez-vous associé (optionnel)',
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Motif de la consultation',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Notes préliminaires',
                ],
            ],
            'required' => ['patient_id', 'doctor_id', 'reason'],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['consultations.create'];
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(User $user, array $parameters): array
    {
        $patient = Patient::find($parameters['patient_id']);
        if (! $patient) {
            return $this->error('Patient non trouvé.');
        }

        $doctor = Doctor::find($parameters['doctor_id']);
        if (! $doctor) {
            return $this->error('Médecin non trouvé.');
        }

        $summary = "Créer une consultation:\n".
            "- Patient: ".trim("{$patient->first_name} {$patient->last_name}")."\n".
            "- Médecin: Dr. ".trim("{$doctor->first_name} {$doctor->last_name}")."\n".
            "- Motif: {$parameters['reason']}";

        if (! empty($parameters['notes'])) {
            $summary .= "\n- Notes: ".mb_substr($parameters['notes'], 0, 200);
        }

        return $this->needsConfirmation($summary, [
            'patient_id' => $patient->id,
            'patient_name' => trim("{$patient->first_name} {$patient->last_name}"),
            'doctor_id' => $doctor->id,
            'doctor_name' => trim("{$doctor->first_name} {$doctor->last_name}"),
            'appointment_id' => $parameters['appointment_id'] ?? null,
            'reason' => $parameters['reason'],
            'notes' => $parameters['notes'] ?? null,
        ]);
    }

    public function executeConfirmed(User $user, array $parameters): array
    {
        try {
            $consultationService = app(\App\Services\ConsultationService::class);

            $data = [
                'patient_id' => $parameters['patient_id'],
                'doctor_id' => $parameters['doctor_id'],
                'appointment_id' => $parameters['appointment_id'] ?? null,
                'consultation_date' => now()->toDateString(),
                'reason' => $parameters['reason'],
                'notes' => $parameters['notes'] ?? null,
            ];

            $consultation = $consultationService->create($data, $user);

            $this->logActivity(
                $user,
                null,
                "Consultation créée pour patient #{$parameters['patient_id']}",
                "Consultation #{$consultation->id} créée",
                $parameters,
                'success',
                "Consultation #{$consultation->id} créée",
            );

            return $this->success([
                'consultation_id' => $consultation->id,
                'date' => $consultation->consultation_date?->format('d/m/Y'),
            ], "Consultation #{$consultation->id} créée avec succès.");
        } catch (\Exception $e) {
            return $this->error('Erreur lors de la création: '.$e->getMessage());
        }
    }
}
