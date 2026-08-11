<?php

namespace App\Services;

use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrescriptionPdfService
{
    /**
     * Génère et télécharge l'ordonnance en PDF (document entièrement français).
     */
    public function download(Prescription $prescription): StreamedResponse
    {
        $prescription->load([
            'patient',
            'doctor',
            'consultation',
            'items',
        ]);

        activity('prescriptions')
            ->performedOn($prescription)
            ->causedBy(Auth::user())
            ->withProperties(['prescription_number' => $prescription->prescription_number])
            ->log('PDF d\'ordonnance téléchargé');

        $pdf = Pdf::loadView('pdf.prescription', [
            'prescription' => $prescription,
            'qrDataUri' => $this->qrDataUri($prescription),
        ])->setPaper('a4', 'portrait');

        $filename = 'ordonnance-'.$prescription->prescription_number.'.pdf';

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * QR Code contenant uniquement le jeton sécurisé de l'ordonnance
     * (aucune donnée médicale ou personnelle en clair).
     */
    private function qrDataUri(Prescription $prescription): ?string
    {
        if (! $prescription->verification_token) {
            return null;
        }

        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'eccLevel' => EccLevel::M,
            'scale' => 5,
            'outputBase64' => false,
        ]);

        $url = route('prescriptions.verify', $prescription->verification_token);

        $png = (new QRCode($options))->render($url);

        return 'data:image/png;base64,'.base64_encode((string) $png);
    }
}
