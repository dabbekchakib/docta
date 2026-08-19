<?php

namespace App\Filament\Patient\Pages;

use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\LaboratoryRequest;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewLabExam extends Page
{
    use HasPatient;
    use InteractsWithInfolists;

    protected string $view = 'filament.patient.pages.view-lab-exam';

    public ?int $labRequestId = null;

    public ?LaboratoryRequest $labRequest = null;

    public function getHeading(): string
    {
        return 'Résultat d\'examen';
    }

    public function mount(int $labRequestId): void
    {
        $this->labRequestId = $labRequestId;

        $patient = $this->getPatient();

        abort_unless($patient, 403);

        $this->labRequest = LaboratoryRequest::query()
            ->where('patient_id', $patient->id)
            ->with(['doctor', 'laboratory', 'items.results', 'report'])
            ->findOrFail($labRequestId);
    }

    public static function getRoutePath(Panel $panel): string
    {
        return '/lab-exam/{labRequestId}';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->record($this->labRequest ??= $this->loadLabRequest())
            ->schema([
                Section::make('Informations de la demande')
                    ->schema([
                        TextEntry::make('request_number')
                            ->label('N° demande'),
                        TextEntry::make('requested_at')
                            ->label('Date')
                            ->date('d/m/Y'),
                        TextEntry::make('doctor.full_name')
                            ->label('Médecin prescripteur'),
                        TextEntry::make('laboratory.name')
                            ->label('Laboratoire')
                            ->placeholder('Non désigné'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),
                        TextEntry::make('priority')
                            ->label('Priorité')
                            ->badge(),
                    ])
                    ->columns(3),
                Section::make('Résultats')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Examens')
                            ->schema([
                                TextEntry::make('test.name')
                                    ->label('Examen'),
                                RepeatableEntry::make('results')
                                    ->label('Résultats')
                                    ->schema([
                                        TextEntry::make('parameter_name')
                                            ->label('Paramètre'),
                                        TextEntry::make('value')
                                            ->label('Valeur'),
                                        TextEntry::make('unit')
                                            ->label('Unité'),
                                        TextEntry::make('reference_text')
                                            ->label('Référence')
                                            ->formatStateUsing(function (TextEntry $component, $state): string {
                                                $record = $component->getRecord();

                                                if (is_string($state) && $state !== '') {
                                                    return $state;
                                                }

                                                $min = $record?->reference_min;
                                                $max = $record?->reference_max;

                                                if ($min !== null && $max !== null) {
                                                    return "{$min} – {$max}";
                                                }

                                                if ($min !== null) {
                                                    return '≥ '.$min;
                                                }

                                                if ($max !== null) {
                                                    return '≤ '.$max;
                                                }

                                                return '—';
                                            }),
                                        TextEntry::make('abnormality')
                                            ->label('Anomalie')
                                            ->badge()
                                            ->color(fn ($state): string => $state?->getColor() ?? 'gray'),
                                    ])
                                    ->columns(5),
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make('Rapport')
                    ->visible(fn (): bool => ($this->labRequest ??= $this->loadLabRequest())?->report !== null)
                    ->schema([
                        TextEntry::make('report.report_number')
                            ->label('N° rapport'),
                        TextEntry::make('report.report_date')
                            ->label('Date du rapport')
                            ->date('d/m/Y'),
                        TextEntry::make('report.summary')
                            ->label('Résumé')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('report.comments')
                            ->label('Commentaires')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    private function loadLabRequest(): LaboratoryRequest
    {
        $patient = $this->getPatient();

        return LaboratoryRequest::query()
            ->where('patient_id', $patient->id)
            ->with(['doctor', 'laboratory', 'items.results', 'report'])
            ->findOrFail($this->labRequestId);
    }
}
