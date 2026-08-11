<?php

namespace App\Services;

use App\Models\Consultation;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsultationPdfService
{
    /**
     * Génère et télécharge le rapport de consultation en PDF.
     */
    public function download(Consultation $consultation): StreamedResponse
    {
        $consultation->load(['patient', 'doctor', 'vitalSign', 'appointment']);

        $pdf = Pdf::loadView('pdf.consultation', ['consultation' => $consultation])
            ->setPaper('a4', 'portrait');

        $filename = 'consultation-'.$consultation->consultation_number.'.pdf';

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
