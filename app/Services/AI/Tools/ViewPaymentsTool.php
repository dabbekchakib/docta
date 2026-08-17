<?php

namespace App\Services\AI\Tools;

use App\Models\Payment;
use App\Models\User;

class ViewPaymentsTool extends BaseAITool
{
    public function getName(): string
    {
        return 'view_payments';
    }

    public function getDescription(): string
    {
        return 'Consulte les paiements. Filtrable par patient, facture ou date.';
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
                'invoice_id' => [
                    'type' => 'integer',
                    'description' => 'ID de la facture',
                ],
                'date_from' => [
                    'type' => 'string',
                    'description' => 'Date de début (YYYY-MM-DD)',
                ],
                'date_to' => [
                    'type' => 'string',
                    'description' => 'Date de fin (YYYY-MM-DD)',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Nombre max de résultats (défaut: 20)',
                ],
            ],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['payments.view'];
    }

    public function execute(User $user, array $parameters): array
    {
        $query = Payment::query()->with(['invoice', 'patient', 'paymentMethod']);

        if (! empty($parameters['patient_id'])) {
            $query->where('patient_id', $parameters['patient_id']);
        }
        if (! empty($parameters['invoice_id'])) {
            $query->where('invoice_id', $parameters['invoice_id']);
        }
        if (! empty($parameters['date_from'])) {
            $query->where('payment_date', '>=', $parameters['date_from']);
        }
        if (! empty($parameters['date_to'])) {
            $query->where('payment_date', '<=', $parameters['date_to']);
        }

        $limit = min($parameters['limit'] ?? 20, 50);

        $payments = $query->orderBy('payment_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'payment_number' => $p->payment_number,
                'date' => $p->payment_date?->format('d/m/Y'),
                'patient' => $p->patient ? trim("{$p->patient->first_name} {$p->patient->last_name}") : null,
                'invoice_number' => $p->invoice?->invoice_number,
                'amount' => $p->amount,
                'method' => $p->paymentMethod?->name,
                'status' => $p->status?->label(),
                'reference' => $p->reference,
            ]);

        $totalAmount = $payments->sum('amount');

        $this->logActivity(
            $user,
            null,
            'Consultation paiements',
            null,
            $parameters,
            'success',
            $payments->count().' paiement(s), total: '.$totalAmount.' TND',
        );

        return $this->success($payments->all(), $payments->count().' paiement(s) trouvé(s). Total: '.$totalAmount.' TND');
    }
}
