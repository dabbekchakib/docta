<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Service;
use App\Support\Money;
use Illuminate\Support\Collection;

/**
 * Calcul des montants d'une facture, entièrement côté serveur.
 *
 * Aucun montant calculé côté client n'est accepté : seules les données de base
 * (lignes, remise) sont fournies, tous les totaux sont recalculés ici.
 */
class InvoiceCalculationService
{
    /**
     * @param  array<int, array{service_id?: int|string|null, description?: string|null, quantity?: string|int|float|null, unit_price?: string|int|float|null, discount_percent?: string|int|float|null, tax_rate?: string|int|float|null}>  $items
     * @return array{
     *     items: array<int, array{service_id: int|null, description: string, quantity: string, unit_price: string, discount_percent: string, tax_rate: string, tax_amount: string, line_total: string, sort_order: int}>,
     *     discount_type: string,
     *     discount_value: string,
     *     subtotal: string,
     *     discount_amount: string,
     *     taxable_base: string,
     *     tax_amount: string,
     *     total: string
     * }
     */
    public function calculate(array $items, string $discountType = 'none', string|int|float $discountValue = 0): array
    {
        abort_if($items === [], 422, 'Une facture doit contenir au moins une ligne.');

        $normalizedItems = $this->normalizeItems($items);

        $subtotal = Money::zero();
        $taxableBase = Money::zero();
        $taxAmount = Money::zero();

        foreach ($normalizedItems as &$line) {
            $base = Money::mul($line['quantity'], $line['unit_price']);

            if (Money::gt($line['discount_percent'], '0')) {
                $lineDiscount = Money::mul($base, Money::div($line['discount_percent'], '100'));
                $lineBase = Money::sub($base, $lineDiscount);
            } else {
                $lineBase = $base;
            }

            $lineTax = Money::mul($lineBase, Money::div($line['tax_rate'], '100'));

            $line['tax_amount'] = $lineTax;
            $line['line_total'] = Money::add($lineBase, $lineTax);

            $subtotal = Money::add($subtotal, $line['line_total']);
            $taxableBase = Money::add($taxableBase, $lineBase);
            $taxAmount = Money::add($taxAmount, $lineTax);
        }
        unset($line);

        $discountAmount = $this->discountAmount($subtotal, $discountType, $discountValue);

        $total = Money::sub($subtotal, $discountAmount);

        return [
            'items' => $normalizedItems,
            'discount_type' => $discountType,
            'discount_value' => Money::normalize((string) $discountValue),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'taxable_base' => $taxableBase,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }

    /**
     * Vérifie qu'un montant d'acompte est valide pour une facture.
     */
    public function assertValidDeposit(string $amount, Invoice $invoice): void
    {
        abort_if(Money::lte($amount, '0'), 422, 'Le montant doit être supérieur à zéro.');
        abort_if(Money::gt($amount, $invoice->amount_remaining), 422, 'Le montant dépasse le solde restant dû de la facture.');
    }

    private function discountAmount(string $subtotal, string $discountType, string|int|float $discountValue): string
    {
        if ($discountType === 'percent') {
            return Money::mul($subtotal, Money::div((string) $discountValue, '100'));
        }

        if ($discountType === 'amount') {
            $value = Money::normalize((string) $discountValue);

            return Money::lt($value, $subtotal) ? $value : $subtotal;
        }

        return Money::zero();
    }

    /**
     * Normalise les lignes et récupère le prix/la taxe par défaut depuis le tarif.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array{service_id: int|null, description: string, quantity: string, unit_price: string, discount_percent: string, tax_rate: string, tax_amount: string, line_total: string, sort_order: int}>
     */
    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach (array_values($items) as $index => $item) {
            $service = $item['service_id'] ?? null
                ? Service::withTrashed()->find((int) $item['service_id'])
                : null;

            $unitPrice = $service
                ? (string) $service->price
                : (string) ($item['unit_price'] ?? 0);

            $taxRate = isset($item['tax_rate']) && $item['tax_rate'] !== ''
                ? (string) $item['tax_rate']
                : (string) ($service?->taxRate?->rate ?? '0');

            $normalized[] = [
                'service_id' => $service?->id,
                'description' => (string) ($item['description'] ?? $service?->name ?? ''),
                'quantity' => Money::normalize((string) ($item['quantity'] ?? '1')),
                'unit_price' => Money::normalize($unitPrice),
                'discount_percent' => Money::round2((string) ($item['discount_percent'] ?? '0')),
                'tax_rate' => Money::round2($taxRate),
                'tax_amount' => Money::zero(),
                'line_total' => Money::zero(),
                'sort_order' => $index + 1,
            ];
        }

        return $normalized;
    }

    public static function sum(Collection $amounts): string
    {
        return $amounts->reduce(
            fn (string $carry, $amount): string => Money::add($carry, (string) $amount),
            Money::zero()
        );
    }
}
