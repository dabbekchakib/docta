<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryReport;
use App\Services\LaboratoryReportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaboratoryReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Téléchargement sécurisé du compte rendu d'analyses (fichier privé).
     */
    public function download(LaboratoryReport $laboratoryReport): StreamedResponse|Response
    {
        $this->authorize('download', $laboratoryReport);

        return app(LaboratoryReportService::class)->download($laboratoryReport);
    }
}
