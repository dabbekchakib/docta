<?php

namespace App\Services\AI\Tools;

use App\Models\Invoice;
use App\Models\User;

class RecordPaymentTool extends BaseAITool
{
    public function getName(): string
    {
        return 'record_payment';
    }

    public function getDescription(): string
    {
        return 'Propose d\'enregistrer un paiement sur une facture. Nécessite confirmation.';
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
                'amount' => [
                    'type' => 'string',
                    'description' => 'Montant du paiement en TND',
                ],
                'payment_method_id' => [
                    'type' => 'integer',
                    'description' => 'ID du moyen de paiement',
                ],
                'reference' => [
                    'type' => 'string',
                    'description' => 'Référence du paiement (chèque, virement, etc.)',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Notes',
                ],
            ],
            'required' => ['invoice_id', 'amount'],
        ];
    }

    public function requiredPermissions(): array
    {
        return ['payments.create'];
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(User $user, array $parameters): array
    {
        $invoice = Invoice::with('patient')->find($parameters['invoice_id']);
        if (! $invoice) {
            return $this->error('Facture non trouvée.');
        }

        $patient = $invoice->patient;
        $remaining = $invoice->amount_remaining;

        $summary = "Enregistrer un paiement:\n".
            "- Facture: {$invoice->invoice_number}\n".
            "- Patient: ".($patient ? trim("{$patient->first_name} {$patient->last_name}") : 'N/A')."\n".
            "- Montant: {$parameters['amount']} TND\n".
            "- Reste à payer: {$remaining} TND";

        return $this->needsConfirmation($summary, [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'patient_name' => $patient ? trim("{$patient->first_name} {$patient->last_name}") : null,
            'amount' => $parameters['amount'],
            'amount_remaining' => $remaining,
            'payment_method_id' => $parameters['payment_method_id'] ?? null,
            'reference' => $parameters['reference'] ?? null,
            'notes' => $parameters['notes'] ?? null,
        ]);
    }

    public function executeConfirmed(User $user, array $parameters): array
    {
        try {
            $paymentService = app(\App\Services\PaymentService::class);

            $data = [
                'invoice_id' => $parameters['invoice_id'],
                'amount' => $parameters['amount'],
                'payment_method_id' => $parameters['payment_method_id'] ?? null,
                'reference' => $parameters['reference'] ?? null,
                'notes' => $parameters['notes'] ?? null,
                'status' => 'completed',
            ];

            $payment = $paymentService->create($data, $user);

            $this->logActivity(
                $user,
                null,
                "Paiement enregistré pour facture #{$parameters['invoice_id']}",
                "Paiement {$payment->payment_number} enregistré",
                $parameters,
                'success',
                "Paiement {$payment->payment_number} - {$parameters['amount']} TND",
            );

            return $this->success([
                'payment_number' => $payment->payment_number,
                'id' => $payment->id,
                'amount' => $payment->amount,
                'status' => $payment->status->label(),
            ], "Paiement {$payment->payment_number} enregistré avec succès ({$parameters['amount']} TND).");
        } catch (\Exception $e) {
            return $this->error('Erreur lors de l\'enregistrement: '.$e->getMessage());
        }
    }
}
