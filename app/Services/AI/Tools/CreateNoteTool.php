<?php

namespace App\Services\AI\Tools;

use App\Models\MedicalRecord;
use App\Models\User;

class CreateNoteTool extends BaseAITool
{
    public function getName(): string
    {
        return 'create_note';
    }

    public function getDescription(): string
    {
        return 'Crée une note ou un commentaire dans le dossier médical d\'un patient. Nécessite confirmation.';
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
                'title' => [
                    'type' => 'string',
                    'description' => 'Titre de la note',
                ],
                'content' => [
                    'type' => 'string',
                    'description' => 'Contenu de la note',
                ],
                'type' => [
                    'type' => 'string',
                    'description' => 'Type: medical_history, surgical_history, family_history',
                ],
            ],
            'required' => ['patient_id', 'title', 'content'],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['medical_records.update', 'medical_histories.manage'];
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(User $user, array $parameters): array
    {
        $patient = \App\Models\Patient::find($parameters['patient_id']);
        if (! $patient) {
            return $this->error('Patient non trouvé.');
        }

        $summary = "Ajouter une note au dossier de ".trim("{$patient->first_name} {$patient->last_name}").":\n".
            "- Titre: {$parameters['title']}\n".
            "- Contenu: ".mb_substr($parameters['content'], 0, 200);

        return $this->needsConfirmation($summary, [
            'patient_id' => $patient->id,
            'patient_name' => trim("{$patient->first_name} {$patient->last_name}"),
            'title' => $parameters['title'],
            'content' => $parameters['content'],
            'type' => $parameters['type'] ?? 'medical_history',
        ]);
    }

    public function executeConfirmed(User $user, array $parameters): array
    {
        try {
            $medicalRecord = MedicalRecord::firstOrCreate(
                ['patient_id' => $parameters['patient_id']],
            );

            $type = $parameters['type'] ?? 'medical_history';

            match ($type) {
                'surgical_history' => $medicalRecord->surgicalHistories()->create([
                    'procedure_name' => $parameters['title'],
                    'description' => $parameters['content'],
                ]),
                'family_history' => $medicalRecord->familyHistories()->create([
                    'condition' => $parameters['title'],
                    'notes' => $parameters['content'],
                ]),
                default => $medicalRecord->medicalHistories()->create([
                    'type' => 'custom',
                    'title' => $parameters['title'],
                    'description' => $parameters['content'],
                ]),
            };

            $this->logActivity(
                $user,
                null,
                "Note ajoutée au dossier patient #{$parameters['patient_id']}",
                "Note '{$parameters['title']}' créée",
                $parameters,
                'success',
                'Note ajoutée avec succès',
            );

            return $this->success([], "Note '{$parameters['title']}' ajoutée au dossier du patient.");
        } catch (\Exception $e) {
            return $this->error('Erreur lors de la création de la note: '.$e->getMessage());
        }
    }
}
