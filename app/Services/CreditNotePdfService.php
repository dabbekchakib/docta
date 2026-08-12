<?php

namespace App\Services;

use App\Models\CreditNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CreditNotePdfService
{
    /**
     * Génère et télécharge l'avoir en PDF (document entièrement français).
     */
    public function download(CreditNote $creditNote): StreamedResponse
    {
        $creditNote->load([
            'patient',
            'invoice',
            'createdBy',
        ]);

        activity('credit_notes')
            ->performedOn($creditNote)
            ->causedBy(Auth::user())
            ->withProperties(['credit_note_number' => $creditNote->credit_note_number])
            ->log('PDF d\'avoir téléchargé');

        $pdf = Pdf::loadView('pdf.credit-note', [
            'creditNote' => $creditNote,
            'cabinet' => app(SettingsService::class)->cabinet(),
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            'avoir-'.$creditNote->credit_note_number.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
