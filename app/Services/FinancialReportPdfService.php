<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportPdfService
{
    public function __construct(
        private readonly FinancialReportService $report,
    ) {}

    /**
     * Génère et télécharge un rapport financier d'ensemble en PDF.
     */
    public function download(array $scope = []): StreamedResponse
    {
        $data = [
            'overview' => $this->report->overview(),
            'daily' => $this->report->dailyCollection(),
            'monthly' => $this->report->monthlyRevenue(),
            'overdue' => $this->report->overdueInvoices(20),
            'scope' => $scope,
        ];

        activity('financial_reports')
            ->causedBy(Auth::user())
            ->log('Rapport financier PDF téléchargé');

        $pdf = Pdf::loadView('pdf.financial-report', [
            ...$data,
            'cabinet' => app(SettingsService::class)->cabinet(),
        ])->setPaper('a4', 'landscape');

        $filename = 'rapport-financier-'.now()->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
