<?php

namespace App\Services\AI\Tools;

use App\Models\Invoice;
use App\Models\User;

class ViewInvoiceTool extends BaseAITool
{
    public function getName(): string
    {
        return 'view_invoice';
    }

    public function getDescription(): string
    {
        return 'Consulte une facture ou la liste des factures. Inclut les détails, les items et les paiements associés.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'invoice_id' => [
                    'type' => 'integer',
                    'description' => 'ID de la facture',
                ],
                'patient_id' => [
                    'type' => 'integer',
                    'description' => 'ID du patient (pour lister ses factures)',
                ],
                'status' => [
                    'type' => 'string',
                    'description' => 'Statut: draft, issued, paid, partially_paid, cancelled, credited',
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
        return ['invoices.view'];
    }

    public function execute(User $user, array $parameters): array
    {
        if (! empty($parameters['invoice_id'])) {
            $invoice = Invoice::query()
                ->with(['patient', 'doctor', 'items', 'payments'])
                ->where('id', $parameters['invoice_id'])
                ->first();

            if (! $invoice) {
                return $this->error('Facture non trouvée.');
            }

            $data = [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'date' => $invoice->created_at?->format('d/m/Y'),
                'patient' => $invoice->patient ? trim("{$invoice->patient->first_name} {$invoice->patient->last_name}") : null,
                'doctor' => $invoice->doctor ? trim("{$invoice->doctor->first_name} {$invoice->doctor->last_name}") : null,
                'status' => $invoice->status?->label(),
                'subtotal' => $invoice->subtotal,
                'discount_amount' => $invoice->discount_amount,
                'tax_amount' => $invoice->tax_amount,
                'stamp_fee' => $invoice->stamp_fee,
                'total' => $invoice->total,
                'amount_paid' => $invoice->amount_paid,
                'amount_remaining' => $invoice->amount_remaining,
                'items' => $invoice->items->map(fn ($i) => [
                    'description' => $i->description,
                    'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price,
                    'total' => $i->line_total,
                ])->toArray(),
                'payments_count' => $invoice->payments()->count(),
            ];

            return $this->success($data, "Facture {$invoice->invoice_number} - Total: {$invoice->total} TND");
        }

        // Liste de factures
        $query = Invoice::query()->with(['patient']);

        if (! empty($parameters['patient_id'])) {
            $query->where('patient_id', $parameters['patient_id']);
        }
        if (! empty($parameters['status'])) {
            $query->where('status', $parameters['status']);
        }

        $limit = min($parameters['limit'] ?? 10, 30);

        $invoices = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'invoice_number' => $i->invoice_number,
                'date' => $i->created_at?->format('d/m/Y'),
                'patient' => $i->patient ? trim("{$i->patient->first_name} {$i->patient->last_name}") : null,
                'status' => $i->status?->label(),
                'total' => $i->total,
                'amount_remaining' => $i->amount_remaining,
            ]);

        return $this->success($invoices->all(), $invoices->count().' facture(s) trouvée(s).');
    }
}
