<?php

namespace App\Filament\Resources\LaboratoryReports\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LaboratoryReportView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Compte rendu')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('report_number')->label('N° compte rendu'),
                        TextEntry::make('report_date')->label('Date')->date('d/m/Y'),
                        TextEntry::make('validated_at')->label('Validé le')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('validatedBy.name')->label('Validé par')->placeholder('—'),
                        TextEntry::make('created_at')->label('Généré le')->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),
                Section::make('Demande liée')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('request.request_number')->label('N° demande'),
                        TextEntry::make('request.requested_at')->label('Date de la demande')->date('d/m/Y'),
                        TextEntry::make('request.patient.full_name')->label('Patient'),
                        TextEntry::make('request.patient.patient_number')->label('N° dossier patient'),
                        TextEntry::make('request.doctor.full_name')->label('Médecin prescripteur'),
                        TextEntry::make('request.laboratory.display_name')->label('Laboratoire')->placeholder('Non désigné'),
                    ])
                    ->columns(3),
                Section::make('Résultats des examens')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('request.items')
                            ->label('Examens')
                            ->schema([
                                TextEntry::make('test.name')->label('Examen'),
                                TextEntry::make('results')
                                    ->label('Résultats')
                                    ->listWithLineBreaks()
                                    ->formatStateUsing(fn ($state, TextEntry $component): string => self::formatResults($state, $component)),
                            ])
                            ->columns(2),
                    ]),
                Section::make('Synthèse et commentaires')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('summary')->label('Synthèse')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('comments')->label('Commentaires')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }

    private static function formatResults(mixed $state, TextEntry $component): string
    {
        $item = $component->getRecord();

        if (! $item || $item->results->isEmpty()) {
            return 'Aucun résultat';
        }

        return $item->results
            ->map(fn ($result): string => collect([
                $result->parameter_name,
                $result->value.($result->unit ? ' '.$result->unit : ''),
                $result->reference_text ?? null,
                $result->abnormality?->getLabel(),
            ])->filter()->implode(' — '))
            ->implode("\n");
    }
}
