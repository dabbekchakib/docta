<?php

namespace App\Services;

use App\Models\MedicalRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MedicalRecordPdfService
{
    /**
     * Génère et télécharge le dossier médical patient en PDF.
     */
    public function download(MedicalRecord $record): StreamedResponse
    {
        $record->load([
            'patient',
            'allergies',
            'chronicDiseases',
            'medicalHistories',
            'surgicalHistories',
            'familyHistories',
            'medications',
            'vaccinations',
            'lifestyle',
        ]);

        $pdf = Pdf::loadView('pdf.medical-record', ['record' => $record])
            ->setPaper('a4', 'portrait');

        $filename = 'dmp-'.$record->medical_record_number.'.pdf';

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
