<?php

namespace App\Filament\Resources\Prescriptions\Schemas;

use App\Enums\PrescriptionStatus;
use App\Models\Prescription;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\HasLabel;
use Spatie\Activitylog\Models\Activity;

class PrescriptionView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('prescription_number')->label('N° ordonnance'),
                        TextEntry::make('prescription_date')->label('Date')->date('d/m/Y'),
                        TextEntry::make('valid_until')->label('Valable jusqu\'au')->date('d/m/Y')->placeholder('—'),
                        TextEntry::make('status')->label('Statut')
                            ->badge()
                            ->color(fn (PrescriptionStatus $state): string => $state->getColor()),
                        TextEntry::make('patient.patient_number')->label('N° dossier patient'),
                        TextEntry::make('patient.full_name')->label('Patient'),
                        TextEntry::make('doctor.full_name')->label('Médecin prescripteur'),
                        TextEntry::make('consultation.consultation_number')->label('Consultation liée')->placeholder('—'),
                        TextEntry::make('createdBy.name')->label('Créé par')->placeholder('—'),
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('notes')->label('Notes')->placeholder('—')->columnSpanFull(),
                    ])
                    ->columns(3),
                Section::make('Médicaments prescrits')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Médicaments')
                            ->schema([
                                TextEntry::make('medicine_name')->label('Médicament'),
                                TextEntry::make('dosage')->label('Dosage'),
                                TextEntry::make('form')->label('Forme')
                                    ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                TextEntry::make('route')->label('Voie')
                                    ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                TextEntry::make('frequency')->label('Fréquence'),
                                TextEntry::make('duration')->label('Durée')
                                    ->formatStateUsing(function (TextEntry $component, $state): string {
                                        $unit = $component->getRecord()?->duration_unit;

                                        return $state !== null
                                            ? $state.' '.self::enumLabel($unit)
                                            : '—';
                                    }),
                                TextEntry::make('quantity')->label('Quantité')->placeholder('—'),
                                TextEntry::make('instructions')->label('Instructions')->placeholder('—')->columnSpanFull(),
                            ])
                            ->columns(6),
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
                                TextEntry::make('causer.name')->label('Utilisateur'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function resolveActivities(RepeatableEntry $component): array
    {
        $prescription = $component->getRecord();

        if (! $prescription instanceof Prescription) {
            return [];
        }

        return $prescription->activities()
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

        if ($state instanceof HasLabel) {
            return $state->getLabel();
        }

        return (string) $state;
    }
}
