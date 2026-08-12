<?php

namespace App\Filament\Resources\LaboratoryRequests\Schemas;

use App\Enums\LaboratoryRequestPriority;
use App\Enums\LaboratoryRequestStatus;
use App\Models\LaboratoryRequest;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Spatie\Activitylog\Models\Activity;

class LaboratoryRequestView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('request_number')->label('N° demande'),
                        TextEntry::make('requested_at')->label('Date')->date('d/m/Y'),
                        TextEntry::make('priority')->label('Priorité')
                            ->badge()
                            ->color(fn (LaboratoryRequestPriority $state): string => $state->getColor()),
                        TextEntry::make('status')->label('Statut')
                            ->badge()
                            ->color(fn (LaboratoryRequestStatus $state): string => $state->getColor()),
                        TextEntry::make('patient.patient_number')->label('N° dossier patient'),
                        TextEntry::make('patient.full_name')->label('Patient'),
                        TextEntry::make('doctor.full_name')->label('Médecin prescripteur'),
                        TextEntry::make('laboratory.display_name')->label('Laboratoire')->placeholder('Non désigné'),
                        TextEntry::make('consultation.consultation_number')->label('Consultation liée')->placeholder('—'),
                        TextEntry::make('createdBy.name')->label('Créé par')->placeholder('—'),
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),
                Section::make('Motif et instructions')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('clinical_information')->label('Informations cliniques')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('doctor_notes')->label('Notes du médecin')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('patient_instructions')->label('Instructions pour le patient')->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make('Examens demandés')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Examens')
                            ->schema([
                                TextEntry::make('test.name')->label('Examen'),
                                TextEntry::make('test.code')->label('Code'),
                                TextEntry::make('sample_type')->label('Prélèvement')
                                    ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                TextEntry::make('status')->label('Statut'),
                                TextEntry::make('instructions')->label('Instructions')->placeholder('—'),
                                TextEntry::make('notes')->label('Notes')->placeholder('—'),
                                ...self::resultsEntries(),
                            ])
                            ->columns(6),
                    ]),
                Section::make('Prélèvements')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('samples')
                            ->label('Prélèvements')
                            ->schema([
                                TextEntry::make('sample_number')->label('N° prélèvement'),
                                TextEntry::make('sample_type')->label('Type')
                                    ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                TextEntry::make('collected_at')->label('Prélevé le')->dateTime('d/m/Y H:i')->placeholder('—'),
                                TextEntry::make('received_at')->label('Reçu le')->dateTime('d/m/Y H:i')->placeholder('—'),
                                TextEntry::make('status')->label('Statut')
                                    ->badge()
                                    ->color(fn (string $state): string => self::sampleColor($state)),
                                TextEntry::make('rejection_reason')->label('Motif de rejet')->placeholder('—')->columnSpanFull(),
                            ])
                            ->columns(4),
                    ]),
                View::make('filament.infolists.dmp-alerts')
                    ->columnSpanFull()
                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()?->patient?->medicalRecord]),
                Section::make('Journal d\'activité')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('activities')
                            ->label('Activités récentes')
                            ->state(fn (RepeatableEntry $component): array => self::resolveActivities($component))
                            ->schema([
                                TextEntry::make('created_at')->label('Date'),
                                TextEntry::make('description')->label('Action'),
                                TextEntry::make('causer')->label('Utilisateur'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    /**
     * Résultats détaillés : visibles uniquement par les utilisateurs autorisés
     * (médecin prescripteur, administrateur, ou permission explicite).
     *
     * @return array<int, RepeatableEntry>
     */
    private static function resultsEntries(): array
    {
        return [
            RepeatableEntry::make('results')
                ->label('Résultats')
                ->schema([
                    TextEntry::make('parameter_name')->label('Paramètre'),
                    TextEntry::make('value')->label('Valeur'),
                    TextEntry::make('unit')->label('Unité'),
                    TextEntry::make('reference_text')->label('Référence')
                        ->formatStateUsing(function (TextEntry $component, $state): string {
                            $record = $component->getRecord();

                            if (is_string($state) && $state !== '') {
                                return $state;
                            }

                            return $record?->reference_min !== null || $record?->reference_max !== null
                                ? self::rangeLabel($record)
                                : '—';
                        }),
                    TextEntry::make('abnormality')->label('Anomalie')
                        ->badge()
                        ->color(fn ($state): string => $state?->getColor() ?? 'gray'),
                ])
                ->columns(5)
                ->visible(fn (RepeatableEntry $component): bool => self::canViewResults($component)),
        ];
    }

    private static function rangeLabel(mixed $record): string
    {
        $min = $record->reference_min;
        $max = $record->reference_max;
        $unit = $record->unit;

        if ($min !== null && $max !== null) {
            return "{$min} – {$max}".($unit ? " {$unit}" : '');
        }

        if ($min !== null) {
            return '≥ '.$min.($unit ? " {$unit}" : '');
        }

        if ($max !== null) {
            return '≤ '.$max.($unit ? " {$unit}" : '');
        }

        return '—';
    }

    private static function canViewResults(RepeatableEntry $component): bool
    {
        $request = $component->getRecord()?->request ?? $component->getParentComponent()?->getRecord();

        if (! $request instanceof LaboratoryRequest) {
            return false;
        }

        return auth()->user()?->can('viewForRequest', $request) ?? false;
    }

    private static function sampleColor(string $state): string
    {
        return match ($state) {
            'rejected' => 'danger',
            'received', 'processed' => 'info',
            'collected' => 'warning',
            default => 'gray',
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function resolveActivities(RepeatableEntry $component): array
    {
        $request = $component->getRecord();

        if (! $request instanceof LaboratoryRequest) {
            return [];
        }

        return $request->activities()
            ->with('causer')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Activity $activity): array => [
                'created_at' => $activity->created_at?->format('d/m/Y H:i') ?? '—',
                'description' => $activity->description,
                'causer' => $activity->causer?->name ?? 'Système',
            ])
            ->all();
    }

    private static function enumLabel(mixed $state): string
    {
        if ($state === null || $state === '') {
            return '—';
        }

        return method_exists($state, 'getLabel') ? $state->getLabel() : (string) $state;
    }
}
