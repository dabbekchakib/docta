<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptPdfService
{
    /**
     * Génère et télécharge le reçu en PDF (document entièrement français).
     */
    public function download(Receipt $receipt): StreamedResponse
    {
        $receipt->load([
            'patient',
            'invoice',
            'payment.paymentMethod',
            'createdBy',
        ]);

        activity('receipts')
            ->performedOn($receipt)
            ->causedBy(Auth::user())
            ->withProperties(['receipt_number' => $receipt->receipt_number])
            ->log('PDF de reçu téléchargé');

        $pdf = Pdf::loadView('pdf.receipt', [
            'receipt' => $receipt,
            'cabinet' => app(SettingsService::class)->cabinet(),
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            'recu-'.$receipt->receipt_number.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Télécharge le reçu associé à un paiement (pré-chargé).
     */
    public function downloadForPayment(Payment $payment): StreamedResponse
    {
        return $this->download($payment->receipt);
    }
}
