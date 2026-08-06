<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Enums\BloodGroup;
use App\Enums\Governorate;
use App\Enums\PatientGender;
use App\Enums\PatientStatus;
use App\Enums\PatientTitle;
use App\Enums\RelationType;
use App\Models\Patient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('patient')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations générales')
                            ->schema([
                                Section::make('Identité')
                                    ->description('Numéro de dossier généré automatiquement à l\'enregistrement.')
                                    ->schema([
                                        Placeholder::make('patient_number')
                                            ->label('N° dossier')
                                            ->content(fn (Placeholder $component): string => $component->getRecord()?->patient_number ?? 'PAT-xxxxxx (généré automatiquement)'),
                                        Select::make('title')
                                            ->label('Civilité')
                                            ->options(PatientTitle::options())
                                            ->default(PatientTitle::Mr->value),
                                        TextInput::make('last_name')
                                            ->label('Nom')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('first_name')
                                            ->label('Prénom')
                                            ->required()
                                            ->maxLength(255),
                                        Select::make('gender')
                                            ->label('Sexe')
                                            ->options(PatientGender::options())
                                            ->required(),
                                        DatePicker::make('birth_date')
                                            ->label('Date de naissance')
                                            ->displayFormat('d/m/Y')
                                            ->before('today')
                                            ->native(false),
                                        Placeholder::make('age')
                                            ->label('Âge')
                                            ->content(fn (Placeholder $component): string => self::resolveAge($component)),
                                        TextInput::make('cin')
                                            ->label('CIN / Passeport')
                                            ->unique(ignoreRecord: true)
                                            ->nullable()
                                            ->maxLength(255),
                                        FileUpload::make('photo')
                                            ->label('Photo')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('patients'),
                                    ])
                                    ->columns(3),
                            ]),
                        Tab::make('Coordonnées')
                            ->schema([
                                Fieldset::make('Coordonnées')
                                    ->schema([
                                        TextInput::make('phone')
                                            ->label('Téléphone principal')
                                            ->tel()
                                            ->regex('/^\+216[2-9][0-9]{7}$/')
                                            ->helperText('Format attendu : +216XXXXXXXX')
                                            ->required(),
                                        TextInput::make('phone_secondary')
                                            ->label('Téléphone secondaire')
                                            ->tel()
                                            ->regex('/^\+216[2-9][0-9]{7}$/')
                                            ->helperText('Format attendu : +216XXXXXXXX')
                                            ->nullable(),
                                        TextInput::make('email')
                                            ->label('Adresse email')
                                            ->email()
                                            ->unique(ignoreRecord: true)
                                            ->nullable(),
                                        TextInput::make('address')
                                            ->label('Adresse')
                                            ->maxLength(255)
                                            ->nullable(),
                                        TextInput::make('city')
                                            ->label('Ville')
                                            ->maxLength(255)
                                            ->nullable(),
                                        Select::make('governorate')
                                            ->label('Gouvernorat')
                                            ->options(Governorate::options())
                                            ->searchable()
                                            ->nullable(),
                                        TextInput::make('postal_code')
                                            ->label('Code postal')
                                            ->maxLength(10)
                                            ->nullable(),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Informations médicales')
                            ->schema([
                                Section::make('Données biométriques')
                                    ->schema([
                                        Select::make('blood_group')
                                            ->label('Groupe sanguin')
                                            ->options(BloodGroup::options())
                                            ->searchable()
                                            ->nullable(),
                                        TextInput::make('height')
                                            ->label('Taille')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(250)
                                            ->suffix('cm')
                                            ->nullable(),
                                        TextInput::make('weight')
                                            ->label('Poids')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(400)
                                            ->suffix('kg')
                                            ->nullable(),
                                    ])
                                    ->columns(3),
                                Section::make('Antécédents & traitements')
                                    ->schema([
                                        Textarea::make('allergies')
                                            ->label('Allergies')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Textarea::make('medical_history')
                                            ->label('Antécédents')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Textarea::make('chronic_diseases')
                                            ->label('Maladies chroniques')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Textarea::make('disability')
                                            ->label('Handicap')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Textarea::make('permanent_treatments')
                                            ->label('Traitements permanents')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Assurance')
                            ->schema([
                                Fieldset::make('CNAM')
                                    ->schema([
                                        Toggle::make('has_cnam')
                                            ->label('Affilié à la CNAM')
                                            ->default(false)
                                            ->inline(),
                                        TextInput::make('cnam_number')
                                            ->label('N° CNAM')
                                            ->maxLength(255)
                                            ->required(fn (Get $get): bool => (bool) $get('has_cnam'))
                                            ->visible(fn (Get $get): bool => (bool) $get('has_cnam')),
                                    ])
                                    ->columns(2),
                                Fieldset::make('Assurance privée')
                                    ->schema([
                                        Toggle::make('has_insurance')
                                            ->label('Patient assuré')
                                            ->default(false)
                                            ->inline(),
                                        TextInput::make('insurance_number')
                                            ->label('N° assuré')
                                            ->maxLength(255)
                                            ->visible(fn (Get $get): bool => (bool) $get('has_insurance')),
                                        DatePicker::make('insurance_expires_at')
                                            ->label('Date d\'expiration')
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->visible(fn (Get $get): bool => (bool) $get('has_insurance')),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Contact d\'urgence')
                            ->schema([
                                Fieldset::make('Contact d\'urgence')
                                    ->schema([
                                        TextInput::make('emergency_contact')
                                            ->label('Nom du contact')
                                            ->maxLength(255),
                                        Select::make('emergency_relation')
                                            ->label('Lien de parenté')
                                            ->options(RelationType::options())
                                            ->nullable(),
                                        TextInput::make('emergency_phone')
                                            ->label('Téléphone')
                                            ->tel()
                                            ->regex('/^\+216[2-9][0-9]{7}$/')
                                            ->nullable(),
                                        TextInput::make('emergency_address')
                                            ->label('Adresse')
                                            ->maxLength(255)
                                            ->nullable(),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Notes & statut')
                            ->schema([
                                Section::make('Notes')
                                    ->schema([
                                        Textarea::make('medical_notes')
                                            ->label('Notes médicales')
                                            ->rows(5)
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Statut du dossier')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Statut')
                                            ->options(PatientStatus::options())
                                            ->default(PatientStatus::Active->value)
                                            ->required(),
                                    ])
                                    ->columns(1),
                            ]),
                    ]),
            ]);
    }

    private static function resolveAge(Placeholder $component): string
    {
        $record = $component->getRecord();

        if (! $record instanceof Patient || ! $record->birth_date) {
            return 'Calculé automatiquement à partir de la date de naissance';
        }

        return $record->age.' ans';
    }
}
