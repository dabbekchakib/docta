<?php

namespace App\Filament\Resources\MedicalRecords\Schemas;

use App\Enums\RhFactor;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class MedicalRecordView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                View::make('filament.infolists.dmp-alerts')
                    ->columnSpanFull()
                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()]),
                Tabs::make('dossier_medical')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Résumé médical')
                            ->schema([
                                Section::make('Dossier')
                                    ->schema([
                                        TextEntry::make('medical_record_number')->label('N° dossier médical'),
                                        TextEntry::make('full_blood_group')->label('Groupe sanguin')->placeholder('Non renseigné'),
                                        TextEntry::make('rh_factor')->label('Facteur Rh')
                                            ->formatStateUsing(fn (RhFactor $state): string => $state->getLabel())
                                            ->placeholder('Non renseigné'),
                                        TextEntry::make('created_at')->label('Ouvert le')->date('d/m/Y'),
                                    ])
                                    ->columns(4),
                                Section::make('Notes générales')
                                    ->schema([
                                        TextEntry::make('general_notes')->label('Notes générales')->markdown()->placeholder('Aucune note.'),
                                        TextEntry::make('emergency_notes')->label('Consignes d\'urgence')
                                            ->markdown()
                                            ->placeholder('Aucune consigne.')
                                            ->color(fn (?string $state): ?string => $state ? 'danger' : null),
                                    ]),
                            ]),
                        Tab::make('Chronologie médicale')
                            ->schema([
                                View::make('filament.infolists.dmp-timeline')
                                    ->columnSpanFull()
                                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()]),
                            ]),
                        Tab::make('Mode de vie')
                            ->schema([
                                View::make('filament.infolists.dmp-lifestyle')
                                    ->columnSpanFull()
                                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()]),
                            ]),
                    ]),
            ]);
    }
}
