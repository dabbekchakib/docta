<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Money;
use Carbon\Carbon;

/**
 * Agrégations financières pour le tableau de bord et les rapports.
 */
class FinancialReportService
{
    /**
     * @return array{
     *     billed: string,
     *     collected: string,
     *     outstanding: string,
     *     invoices: int,
     *     paid_invoices: int,
     *     overdue_invoices: int
     * }
     */
    public function overview(): array
    {
        $billed = (string) Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Paid, InvoiceStatus::Overdue, InvoiceStatus::Credited])
            ->sum('total');

        $collected = (string) Payment::query()
            ->where('status', PaymentStatus::Completed)
            ->sum('amount');

        $outstanding = (string) Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])
            ->sum('amount_remaining');

        return [
            'billed' => Money::normalize($billed),
            'collected' => Money::normalize($collected),
            'outstanding' => Money::normalize($outstanding),
            'invoices' => Invoice::query()->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Paid, InvoiceStatus::Overdue])->count(),
            'paid_invoices' => Invoice::query()->where('status', InvoiceStatus::Paid)->count(),
            'overdue_invoices' => Invoice::query()->where('status', InvoiceStatus::Overdue)->count(),
        ];
    }

    /**
     * Encaissements d'une journée.
     *
     * @return array<string, mixed>
     */
    public function dailyCollection(?Carbon $date = null): array
    {
        $date ??= now();

        $payments = Payment::query()
            ->where('status', PaymentStatus::Completed)
            ->whereDate('payment_date', $date->toDateString())
            ->with('paymentMethod', 'invoice.patient')
            ->get();

        $total = Money::normalize((string) $payments->sum('amount'));

        $byMethod = $payments
            ->groupBy(fn (Payment $payment): string => $payment->paymentMethod?->name ?? 'Autre')
            ->map(fn ($group): string => Money::normalize((string) $group->sum('amount')))
            ->sortKeys()
            ->all();

        return [
            'date' => $date,
            'total' => $total,
            'count' => $payments->count(),
            'payments' => $payments,
            'byMethod' => $byMethod,
        ];
    }

    /**
     * Factures en retard non soldées.
     */
    public function overdueInvoices(int $limit = 50)
    {
        return Invoice::query()
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])
            ->whereDate('due_date', '<', now()->toDateString())
            ->with('patient')
            ->orderBy('due_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Chiffre d'affaires mensuel facturé et encaissé.
     *
     * @return array{
     *     billed: string,
     *     collected: string
     * }
     */
    public function monthlyRevenue(?int $year = null, ?int $month = null): array
    {
        $year ??= now()->year;
        $month ??= now()->month;

        $billed = (string) Invoice::query()
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Paid, InvoiceStatus::Overdue, InvoiceStatus::Credited])
            ->sum('total');

        $collected = (string) Payment::query()
            ->where('status', PaymentStatus::Completed)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->sum('amount');

        return [
            'billed' => Money::normalize($billed),
            'collected' => Money::normalize($collected),
        ];
    }
}
