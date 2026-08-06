<?php

namespace App\Filament\Resources\Doctors\Schemas;

use App\Enums\DoctorStatus;
use App\Models\Doctor;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\HasLabel;
use Spatie\Activitylog\Models\Activity;

class DoctorView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('fiche_medecin')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations générales')
                            ->schema([
                                Section::make('Identité')
                                    ->schema([
                                        SpatieMediaLibraryImageEntry::make('photo')
                                            ->label('Photo')
                                            ->collection('photo')
                                            ->circular(),
                                        TextEntry::make('doctor_code')->label('Code médecin'),
                                        TextEntry::make('full_name')->label('Nom complet'),
                                        TextEntry::make('gender')->label('Sexe')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('birth_date')->label('Date de naissance')->date('d/m/Y')->placeholder('—'),
                                        TextEntry::make('national_id')->label('CIN')->placeholder('—'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn (DoctorStatus $state): string => $state->getColor()),
                                        TextEntry::make('user.name')->label('Compte utilisateur')->placeholder('Non lié'),
                                    ])
                                    ->columns(3),
                                Section::make('Activité professionnelle')
                                    ->schema([
                                        TextEntry::make('speciality')->label('Spécialité')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('sub_speciality')->label('Sous-spécialité')->placeholder('—'),
                                        TextEntry::make('order_number')->label('N° d\'ordre')->placeholder('—'),
                                        TextEntry::make('start_working_date')->label('Date de recrutement')->date('d/m/Y')->placeholder('—'),
                                        TextEntry::make('consultation_fee')->label('Honoraires')
                                            ->money('TND')
                                            ->placeholder('—'),
                                        TextEntry::make('consultation_duration')->label('Durée de consultation')
                                            ->formatStateUsing(fn (?int $state): string => $state ? $state.' min' : '—'),
                                    ])
                                    ->columns(3),
                                Section::make('Coordonnées')
                                    ->schema([
                                        TextEntry::make('email')->label('Adresse email')->placeholder('—'),
                                        TextEntry::make('phone')->label('Téléphone')->placeholder('—'),
                                        TextEntry::make('mobile')->label('Mobile')->placeholder('—'),
                                        TextEntry::make('address')->label('Adresse')->placeholder('—'),
                                        TextEntry::make('city')->label('Ville')->placeholder('—'),
                                        TextEntry::make('governorate')->label('Gouvernorat')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('postal_code')->label('Code postal')->placeholder('—'),
                                    ])
                                    ->columns(2),
                                Section::make('Biographie')
                                    ->schema([
                                        TextEntry::make('biography')
                                            ->label('Biographie')
                                            ->html()
                                            ->placeholder('Aucune biographie renseignée')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Patients')
                            ->schema([
                                Section::make('Aucun patient suivi pour le moment')
                                    ->description('La liste des patients sera disponible avec le module Rendez-vous (relation préparée).')
                                    ->schema([]),
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
                        Tab::make('Facturation')
                            ->schema([
                                Section::make('Aucune facture enregistrée')
                                    ->description('Le module Facturation sera disponible dans la Phase 5.')
                                    ->schema([]),
                            ]),
                        Tab::make('Documents')
                            ->schema([
                                Section::make('Signature numérique')
                                    ->schema([
                                        SpatieMediaLibraryImageEntry::make('signature')
                                            ->label('Signature')
                                            ->collection('signature')
                                            ->defaultImageUrl(null),
                                    ]),
                                Section::make('Diplôme')
                                    ->schema([
                                        TextEntry::make('diploma')
                                            ->label('Diplôme')
                                            ->state(fn (Doctor $record): string => $record->getFirstMedia('diploma')?->file_name ?? 'Aucun diplôme joint'),
                                    ]),
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
        $doctor = $component->getRecord();

        if (! $doctor instanceof Doctor) {
            return [];
        }

        return $doctor->activities()
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
