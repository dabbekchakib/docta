<?php

namespace App\Services\AI\Tools;

use App\Models\User;

class SearchPatientsTool extends BaseAITool
{
    public function getName(): string
    {
        return 'search_patients';
    }

    public function getDescription(): string
    {
        return 'Recherche des patients par nom, prénom, numéro de patient, téléphone, email ou CIN. Retourne une liste de patients correspondants.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Terme de recherche (nom, prénom, numéro, téléphone, email ou CIN)',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Nombre maximum de résultats (défaut: 10)',
                ],
            ],
            'required' => ['query'],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['patients.view'];
    }

    public function execute(User $user, array $parameters): array
    {
        $query = $parameters['query'];
        $limit = min($parameters['limit'] ?? 10, 20);

        $patients = \App\Models\Patient::query()
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                    ->orWhere('last_name', 'LIKE', "%{$query}%")
                    ->orWhere('patient_number', 'LIKE', "%{$query}%")
                    ->orWhere('phone', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('cin', 'LIKE', "%{$query}%");
            })
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'patient_number' => $p->patient_number,
                'full_name' => trim("{$p->first_name} {$p->last_name}"),
                'phone' => $p->phone,
                'email' => $p->email,
                'status' => $p->status?->value,
            ]);

        $this->logActivity(
            $user,
            null,
            "Recherche patient: {$query}",
            null,
            $parameters,
            'success',
            $patients->count().' résultat(s) trouvé(s)',
        );

        if ($patients->isEmpty()) {
            return $this->success([], 'Aucun patient trouvé pour "'.$query.'".');
        }

        return $this->success($patients->all(), $patients->count().' patient(s) trouvé(s).');
    }
}
