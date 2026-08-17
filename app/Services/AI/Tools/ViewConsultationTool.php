<?php

namespace App\Services\AI\Tools;

use App\Models\Consultation;
use App\Models\User;

class ViewConsultationTool extends BaseAITool
{
    public function getName(): string
    {
        return 'view_consultation';
    }

    public function getDescription(): string
    {
        return 'Consulte les détails d\'une consultation médicale. Inclut le diagnostic, les observations et les signes vitaux.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'consultation_id' => [
                    'type' => 'integer',
                    'description' => 'ID de la consultation',
                ],
                'patient_id' => [
                    'type' => 'integer',
                    'description' => 'ID du patient (pour lister ses consultations)',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Nombre max de résultats si patient_id (défaut: 10)',
                ],
            ],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['consultations.view', 'consultations.manage'];
    }

    public function execute(User $user, array $parameters): array
    {
        if (! empty($parameters['consultation_id'])) {
            $consultation = Consultation::query()
                ->with(['patient', 'doctor', 'vitalSign', 'prescriptions'])
                ->where('id', $parameters['consultation_id'])
                ->first();

            if (! $consultation) {
                return $this->error('Consultation non trouvée.');
            }

            $data = [
                'id' => $consultation->id,
                'date' => $consultation->consultation_date?->format('d/m/Y'),
                'patient' => $consultation->patient ? trim("{$consultation->patient->first_name} {$consultation->patient->last_name}") : null,
                'doctor' => $consultation->doctor ? trim("{$consultation->doctor->first_name} {$consultation->doctor->last_name}") : null,
                'reason' => $consultation->reason,
                'diagnosis' => $consultation->diagnosis,
                'observations' => $consultation->observations,
                'notes' => $consultation->notes,
                'status' => $consultation->status?->label(),
                'type' => $consultation->type?->label(),
            ];

            if ($consultation->vitalSign) {
                $data['vital_signs'] = [
                    'weight' => $consultation->vitalSign->weight,
                    'height' => $consultation->vitalSign->height,
                    'temperature' => $consultation->vitalSign->temperature,
                    'blood_pressure' => $consultation->vitalSign->blood_pressure,
                    'heart_rate' => $consultation->vitalSign->heart_rate,
                ];
            }

            if ($consultation->prescriptions->isNotEmpty()) {
                $data['prescriptions'] = $consultation->prescriptions->map(fn ($p) => [
                    'id' => $p->id,
                    'status' => $p->status?->label(),
                    'items_count' => $p->items()->count(),
                ])->toArray();
            }

            return $this->success($data, "Consultation #{$consultation->id} du {$data['date']}");
        }

        if (! empty($parameters['patient_id'])) {
            $limit = min($parameters['limit'] ?? 10, 30);

            $consultations = Consultation::query()
                ->with(['doctor'])
                ->where('patient_id', $parameters['patient_id'])
                ->orderBy('consultation_date', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'date' => $c->consultation_date?->format('d/m/Y'),
                    'doctor' => $c->doctor ? trim("{$c->doctor->first_name} {$c->doctor->last_name}") : null,
                    'reason' => $c->reason,
                    'diagnosis' => $c->diagnosis,
                    'status' => $c->status?->label(),
                ]);

            return $this->success($consultations->all(), $consultations->count().' consultation(s) trouvée(s).');
        }

        return $this->error('Veuillez fournir un ID de consultation ou un ID de patient.');
    }
}
