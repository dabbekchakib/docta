<?php

namespace App\Listeners;

use App\Events\CreditNoteIssued;
use App\Events\InvoiceCancelled;
use App\Events\InvoiceIssued;
use App\Events\PaymentCompleted;
use App\Events\RefundCompleted;
use App\Services\JournalEntryService;
use Closure;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Génère automatiquement les écritures comptables lors des opérations de
 * facturation. Une défaillance (plan comptable non installé, compte manquant…)
 * est journalisée mais ne bloque jamais le flux métier.
 */
class AccountingEntriesListener
{
    public function handleInvoiceIssued(InvoiceIssued $event): void
    {
        $this->safe(fn () => app(JournalEntryService::class)->postInvoiceIssued($event->invoice, $event->actor));
    }

    public function handleInvoiceCancelled(InvoiceCancelled $event): void
    {
        $this->safe(fn () => app(JournalEntryService::class)->postInvoiceCancelled($event->invoice, $event->actor));
    }

    public function handlePaymentCompleted(PaymentCompleted $event): void
    {
        $this->safe(fn () => app(JournalEntryService::class)->postPayment($event->payment, $event->actor));
    }

    public function handleCreditNoteIssued(CreditNoteIssued $event): void
    {
        $this->safe(fn () => app(JournalEntryService::class)->postCreditNote($event->creditNote, $event->actor));
    }

    public function handleRefundCompleted(RefundCompleted $event): void
    {
        $this->safe(fn () => app(JournalEntryService::class)->postRefund($event->refund, $event->actor));
    }

    private function safe(Closure $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            Log::warning('Écriture comptable automatique non générée : '.$e->getMessage());
        }
    }
}
