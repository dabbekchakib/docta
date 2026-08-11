<?php

namespace App\Http\Controllers;

use App\Models\MedicalDocument;
use App\Services\MedicalDocumentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MedicalDocumentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Téléchargement sécurisé d'un document médical (fichier privé).
     */
    public function download(MedicalDocument $medicalDocument): StreamedResponse|Response
    {
        $this->authorize('download', $medicalDocument);

        return app(MedicalDocumentService::class)->download($medicalDocument);
    }
}
