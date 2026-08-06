<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Enums\PatientStatus;
use App\Models\Patient;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\HasLabel;
use Spatie\Activitylog\Models\Activity;

class PatientView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('fiche_patient')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations générales')
                            ->schema([
                                Section::make('Identité')
                                    ->schema([
                                        TextEntry::make('patient_number')->label('N° dossier'),
                                        TextEntry::make('full_name')->label('Nom complet'),
                                        TextEntry::make('title')->label('Civilité')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('gender')->label('Sexe')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('birth_date')->label('Date de naissance')->date('d/m/Y'),
                                        TextEntry::make('age')->label('Âge')
                                            ->formatStateUsing(fn (?int $state): string => $state ? $state.' ans' : '—'),
                                        TextEntry::make('cin')->label('CIN / Passeport')->placeholder('—'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn (PatientStatus $state): string => $state->getColor()),
                                    ])
                                    ->columns(3),
                                Section::make('Coordonnées')
                                    ->schema([
                                        TextEntry::make('phone')->label('Téléphone principal'),
                                        TextEntry::make('phone_secondary')->label('Téléphone secondaire')->placeholder('—'),
                                        TextEntry::make('email')->label('Adresse email')->placeholder('—'),
                                        TextEntry::make('address')->label('Adresse')->placeholder('—'),
                                        TextEntry::make('city')->label('Ville')->placeholder('—'),
                                        TextEntry::make('governorate')->label('Gouvernorat')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('postal_code')->label('Code postal')->placeholder('—'),
                                    ])
                                    ->columns(2),
                                Section::make('Assurance')
                                    ->schema([
                                        TextEntry::make('has_cnam')->label('Affilié CNAM')
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Oui' : 'Non'),
                                        TextEntry::make('cnam_number')->label('N° CNAM')->placeholder('—'),
                                        TextEntry::make('has_insurance')->label('Assurance privée')
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Oui' : 'Non'),
                                        TextEntry::make('insurance_number')->label('N° assuré')->placeholder('—'),
                                        TextEntry::make('insurance_expires_at')->label('Expiration')->date('d/m/Y')->placeholder('—'),
                                    ])
                                    ->columns(2),
                                Section::make('Contact d\'urgence')
                                    ->schema([
                                        TextEntry::make('emergency_contact')->label('Nom')->placeholder('—'),
                                        TextEntry::make('emergency_relation')->label('Lien de parenté')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('emergency_phone')->label('Téléphone')->placeholder('—'),
                                        TextEntry::make('emergency_address')->label('Adresse')->placeholder('—'),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Rendez-vous')
                            ->schema([
                                Section::make('Aucun rendez-vous enregistré')
                                    ->description('Le module Rendez-vous sera disponible dans la Phase 3.')
                                    ->schema([]),
                            ]),
                        Tab::make('Consultations')
                            ->schema([
                                Section::make('Aucune consultation enregistrée')
                                    ->description('Le module Consultations sera disponible dans la Phase 4.')
                                    ->schema([]),
                            ]),
                        Tab::make('Ordonnances')
                            ->schema([
                                Section::make('Aucune ordonnance enregistrée')
                                    ->description('Le module Ordonnances sera disponible dans la Phase 4.')
                                    ->schema([]),
                            ]),
                        Tab::make('Factures')
                            ->schema([
                                Section::make('Aucune facture enregistrée')
                                    ->description('Le module Facturation sera disponible dans la Phase 5.')
                                    ->schema([]),
                            ]),
                        Tab::make('Documents')
                            ->schema([
                                Section::make('Aucun document enregistré')
                                    ->description('La gestion des documents sera disponible dans la Phase 4.')
                                    ->schema([]),
                            ]),
                        Tab::make('Journal d\'activité')
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
                    ]),
            ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function resolveActivities(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->activities()
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
