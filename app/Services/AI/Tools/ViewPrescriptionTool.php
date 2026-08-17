<?php

namespace App\Services\AI\Tools;

use App\Models\Prescription;
use App\Models\User;

class ViewPrescriptionTool extends BaseAITool
{
    public function getName(): string
    {
        return 'view_prescription';
    }

    public function getDescription(): string
    {
        return 'Consulte une ordonnance ou la liste des ordonnances d\'un patient. Inclut les médicaments prescrits.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'prescription_id' => [
                    'type' => 'integer',
                    'description' => 'ID de l\'ordonnance',
                ],
                'patient_id' => [
                    'type' => 'integer',
                    'description' => 'ID du patient (pour lister ses ordonnances)',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Nombre max de résultats (défaut: 10)',
                ],
            ],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['prescriptions.view'];
    }

    public function execute(User $user, array $parameters): array
    {
        if (! empty($parameters['prescription_id'])) {
            $prescription = Prescription::query()
                ->with(['patient', 'doctor', 'items'])
                ->where('id', $parameters['prescription_id'])
                ->first();

            if (! $prescription) {
                return $this->error('Ordonnance non trouvée.');
            }

            $data = [
                'id' => $prescription->id,
                'patient' => $prescription->patient ? trim("{$prescription->patient->first_name} {$prescription->patient->last_name}") : null,
                'doctor' => $prescription->doctor ? trim("{$prescription->doctor->first_name} {$prescription->doctor->last_name}") : null,
                'date' => $prescription->created_at?->format('d/m/Y'),
                'status' => $prescription->status?->label(),
                'items' => $prescription->items->map(fn ($i) => [
                    'medicine' => $i->medicine_name,
                    'dosage' => $i->dosage,
                    'frequency' => $i->frequency,
                    'duration' => $i->duration.' '.$i->duration_unit?->label(),
                    'instructions' => $i->instructions,
                ])->toArray(),
            ];

            return $this->success($data, "Ordonnance #{$prescription->id}");
        }

        if (! empty($parameters['patient_id'])) {
            $limit = min($parameters['limit'] ?? 10, 30);

            $prescriptions = Prescription::query()
                ->with(['doctor'])
                ->where('patient_id', $parameters['patient_id'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'doctor' => $p->doctor ? trim("{$p->doctor->first_name} {$p->doctor->last_name}") : null,
                    'date' => $p->created_at?->format('d/m/Y'),
                    'status' => $p->status?->label(),
                    'items_count' => $p->items()->count(),
                ]);

            return $this->success($prescriptions->all(), $prescriptions->count().' ordonnance(s) trouvée(s).');
        }

        return $this->error('Veuillez fournir un ID d\'ordonnance ou un ID de patient.');
    }
}
