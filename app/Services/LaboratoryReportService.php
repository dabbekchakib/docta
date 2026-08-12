<?php

namespace App\Services;

use App\Models\LaboratoryReport;
use App\Models\LaboratoryRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaboratoryReportService
{
    /**
     * Génère un numéro de compte rendu unique (CR-LAB-000001).
     */
    public function generateNumber(): string
    {
        $sequence = LaboratoryReport::max('id') ?? 0;

        return 'CR-LAB-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Génère le compte rendu d'analyses (enregistrement + PDF attaché).
     * Idempotent : retourne le compte rendu existant s'il est déjà généré.
     */
    public function generate(LaboratoryRequest $request, ?User $actor = null): LaboratoryReport
    {
        $actor ??= auth()->user();

        abort_unless($request->isValidated(), 409, 'Le compte rendu ne peut être généré qu\'après validation biologique des résultats.');

        if ($request->report) {
            return $request->report;
        }

        $request->load([
            'items.test',
            'items.results',
            'patient',
            'doctor',
            'laboratory',
        ]);

        $report = LaboratoryReport::create([
            'laboratory_request_id' => $request->id,
            'report_number' => $this->generateNumber(),
            'report_date' => now()->toDateString(),
            'summary' => $this->buildSummary($request),
            'comments' => null,
            'validated_at' => now(),
            'validated_by' => $actor?->id,
        ]);

        $this->attachPdf($report);

        activity('laboratory_reports')
            ->performedOn($report)
            ->causedBy($actor)
            ->withProperties([
                'report_number' => $report->report_number,
                'request_number' => $request->request_number,
            ])
            ->log('Compte rendu généré');

        return $report->load('request');
    }

    /**
     * Téléchargement sécurisé du PDF du compte rendu.
     */
    public function download(LaboratoryReport $report): StreamedResponse
    {
        $report->load([
            'request.patient',
            'request.doctor',
            'request.laboratory',
            'request.items.test',
            'request.items.results',
            'validatedBy',
        ]);

        activity('laboratory_reports')
            ->performedOn($report)
            ->causedBy(Auth::user())
            ->withProperties(['report_number' => $report->report_number])
            ->log('Compte rendu téléchargé');

        $media = $report->getFirstMedia('laboratory_reports');

        if ($media) {
            $stream = $media->stream();

            return response()->streamDownload(
                static function () use ($stream): void {
                    fpassthru($stream);
                },
                $media->file_name,
                ['Content-Type' => 'application/pdf']
            );
        }

        $pdf = Pdf::loadView('pdf.laboratory-report', ['report' => $report])
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            static fn () => print($pdf->output()),
            'compte-rendu-'.$report->report_number.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Rendu du PDF (utilisé aussi pour générer le fichier attaché).
     */
    private function renderPdf(LaboratoryReport $report): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdf.laboratory-report', ['report' => $report])
            ->setPaper('a4', 'portrait');
    }

    private function attachPdf(LaboratoryReport $report): void
    {
        $report->load([
            'request.patient',
            'request.doctor',
            'request.laboratory',
            'request.items.test',
            'request.items.results',
            'validatedBy',
        ]);

        $filename = 'compte-rendu-'.$report->report_number.'.pdf';

        $report
            ->addMediaFromString($this->renderPdf($report)->output())
            ->usingFileName($filename)
            ->usingName($report->report_number)
            ->withResponsiveImages()
            ->toMediaCollection('laboratory_reports');
    }

    private function buildSummary(LaboratoryRequest $request): ?string
    {
        $tests = $request->items
            ->pluck('test.name')
            ->filter()
            ->implode(' + ');

        if ($tests === '') {
            return null;
        }

        $critical = $request->results
            ->filter(fn ($result): bool => $result->isCritical())
            ->count();

        $anomalies = $request->results
            ->filter(fn ($result): bool => $result->isOutOfRange())
            ->count();

        $parts = [$tests];

        if ($anomalies > 0) {
            $parts[] = $anomalies.' valeur(s) hors intervalle de référence';
        }

        if ($critical > 0) {
            $parts[] = $critical.' résultat(s) critique(s)';
        }

        return implode(' — ', $parts);
    }
}
