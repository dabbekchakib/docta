<?php

namespace App\Services\AI\Tools;

use App\Models\Patient;
use App\Models\User;

class CreateInvoiceTool extends BaseAITool
{
    public function getName(): string
    {
        return 'create_invoice';
    }

    public function getDescription(): string
    {
        return 'Propose de créer une facture pour un patient avec des prestations. Nécessite confirmation.';
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
                'items' => [
                    'type' => 'array',
                    'description' => 'Prestations à facturer',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'service_id' => ['type' => 'integer', 'description' => 'ID de la prestation'],
                            'description' => ['type' => 'string', 'description' => 'Description'],
                            'quantity' => ['type' => 'number', 'description' => 'Quantité'],
                            'unit_price' => ['type' => 'string', 'description' => 'Prix unitaire (TND)'],
                        ],
                        'required' => ['description', 'quantity', 'unit_price'],
                    ],
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Notes sur la facture',
                ],
            ],
            'required' => ['patient_id', 'items'],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['invoices.create'];
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

        if (empty($parameters['items'])) {
            return $this->error('Au moins une prestation est requise.');
        }

        // Calculer le total
        $total = 0;
        $itemsSummary = [];
        foreach ($parameters['items'] as $item) {
            $qty = $item['quantity'] ?? 1;
            $lineTotal = (float) $item['unit_price'] * (float) $qty;
            $total += $lineTotal;
            $itemsSummary[] = "- {$item['description']}: {$qty} x {$item['unit_price']} = ".number_format($lineTotal, 3).' TND';
        }

        $summary = "Créer une facture:\n".
            "- Patient: ".trim("{$patient->first_name} {$patient->last_name}")."\n".
            "- Prestations:\n".implode("\n", $itemsSummary)."\n".
            "- Total: ".number_format($total, 3).' TND';

        return $this->needsConfirmation($summary, [
            'patient_id' => $patient->id,
            'patient_name' => trim("{$patient->first_name} {$patient->last_name}"),
            'doctor_id' => $parameters['doctor_id'] ?? null,
            'items' => $parameters['items'],
            'total' => number_format($total, 3),
            'notes' => $parameters['notes'] ?? null,
        ]);
    }

    public function executeConfirmed(User $user, array $parameters): array
    {
        try {
            $invoiceService = app(\App\Services\InvoiceService::class);

            $data = [
                'patient_id' => $parameters['patient_id'],
                'doctor_id' => $parameters['doctor_id'] ?? null,
                'notes' => $parameters['notes'] ?? null,
            ];

            $items = array_map(fn ($item) => [
                'service_id' => $item['service_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'],
            ], $parameters['items']);

            $invoice = $invoiceService->create($data, $items, $user);

            $this->logActivity(
                $user,
                null,
                "Facture créée pour patient #{$parameters['patient_id']}",
                "Facture {$invoice->invoice_number} créée",
                $parameters,
                'success',
                "Facture {$invoice->invoice_number} créée - Total: {$invoice->total} TND",
            );

            return $this->success([
                'invoice_number' => $invoice->invoice_number,
                'id' => $invoice->id,
                'total' => $invoice->total,
                'status' => $invoice->status->label(),
            ], "Facture {$invoice->invoice_number} créée avec succès.");
        } catch (\Exception $e) {
            return $this->error('Erreur lors de la création: '.$e->getMessage());
        }
    }
}
