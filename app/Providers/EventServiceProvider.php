<?php

namespace App\Providers;

use App\Events\CreditNoteIssued;
use App\Events\InvoiceCancelled;
use App\Events\InvoiceIssued;
use App\Events\PaymentCompleted;
use App\Events\RefundCompleted;
use App\Listeners\AccountingEntriesListener;
use Illuminate\Events\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Enregistrement explicite des événements (la découverte automatique
     * n'est pas activée dans cette application).
     */
    public function boot(): void
    {
        Event::listen(InvoiceIssued::class, [AccountingEntriesListener::class, 'handleInvoiceIssued']);
        Event::listen(InvoiceCancelled::class, [AccountingEntriesListener::class, 'handleInvoiceCancelled']);
        Event::listen(PaymentCompleted::class, [AccountingEntriesListener::class, 'handlePaymentCompleted']);
        Event::listen(CreditNoteIssued::class, [AccountingEntriesListener::class, 'handleCreditNoteIssued']);
        Event::listen(RefundCompleted::class, [AccountingEntriesListener::class, 'handleRefundCompleted']);
    }
}
