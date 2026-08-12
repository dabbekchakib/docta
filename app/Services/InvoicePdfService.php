<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoicePdfService
{
    /**
     * Génère et télécharge la facture en PDF (document entièrement français).
     */
    public function download(Invoice $invoice): StreamedResponse
    {
        $invoice->load([
            'patient',
            'doctor',
            'consultation',
            'items.service',
            'payments',
            'createdBy',
        ]);

        activity('invoices')
            ->performedOn($invoice)
            ->causedBy(Auth::user())
            ->withProperties(['invoice_number' => $invoice->invoice_number])
            ->log('PDF de facture téléchargé');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'cabinet' => app(SettingsService::class)->cabinet(),
        ])->setPaper('a4', 'portrait');

        $filename = 'facture-'.$invoice->invoice_number.'.pdf';

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
