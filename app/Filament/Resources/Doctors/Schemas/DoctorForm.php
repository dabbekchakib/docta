<?php

namespace App\Filament\Resources\Doctors\Schemas;

use App\Enums\DoctorGender;
use App\Enums\DoctorStatus;
use App\Enums\Governorate;
use App\Enums\MedicalSpecialty;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class DoctorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('doctor')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations personnelles')
                            ->schema([
                                Section::make('Identité')
                                    ->description('Le code médecin est généré automatiquement à l\'enregistrement.')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Placeholder::make('doctor_code')
                                                    ->label('Code médecin')
                                                    ->content(fn (Placeholder $component): string => $component->getRecord()?->doctor_code ?? 'DOC-xxxxxx (généré automatiquement)'),
                                                Select::make('gender')
                                                    ->label('Sexe')
                                                    ->options(DoctorGender::options())
                                                    ->required(),
                                                DatePicker::make('birth_date')
                                                    ->label('Date de naissance')
                                                    ->displayFormat('d/m/Y')
                                                    ->before('today')
                                                    ->native(false),
                                            ]),
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('last_name')
                                                    ->label('Nom')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('first_name')
                                                    ->label('Prénom')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('national_id')
                                                    ->label('CIN')
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true),
                                            ]),
                                    ]),
                                Section::make('Photo')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('photo')
                                            ->label('Photo du médecin')
                                            ->collection('photo')
                                            ->image()
                                            ->imageEditor()
                                            ->avatar()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Coordonnées')
                            ->schema([
                                Section::make('Coordonnées')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('email')
                                                    ->label('Adresse email')
                                                    ->email()
                                                    ->unique(ignoreRecord: true),
                                                TextInput::make('phone')
                                                    ->label('Téléphone')
                                                    ->tel()
                                                    ->regex('/^\+216[2-9][0-9]{7}$/')
                                                    ->helperText('Format attendu : +216XXXXXXXX'),
                                                TextInput::make('mobile')
                                                    ->label('Mobile')
                                                    ->tel()
                                                    ->regex('/^\+216[2-9][0-9]{7}$/')
                                                    ->helperText('Format attendu : +216XXXXXXXX'),
                                                TextInput::make('postal_code')
                                                    ->label('Code postal')
                                                    ->maxLength(10),
                                            ]),
                                        Textarea::make('address')
                                            ->label('Adresse')
                                            ->rows(2),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('city')
                                                    ->label('Ville')
                                                    ->maxLength(255),
                                                Select::make('governorate')
                                                    ->label('Gouvernorat')
                                                    ->options(Governorate::options())
                                                    ->searchable(),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Informations professionnelles')
                            ->schema([
                                Section::make('Activité')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('speciality')
                                                    ->label('Spécialité')
                                                    ->options(MedicalSpecialty::options())
                                                    ->searchable()
                                                    ->required(),
                                                TextInput::make('sub_speciality')
                                                    ->label('Sous-spécialité')
                                                    ->maxLength(255),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('order_number')
                                                    ->label('N° d\'ordre')
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true),
                                                DatePicker::make('start_working_date')
                                                    ->label('Date de recrutement')
                                                    ->displayFormat('d/m/Y')
                                                    ->native(false),
                                            ]),
                                    ]),
                                Section::make('Consultation')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('consultation_fee')
                                                    ->label('Honoraires de consultation')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->step(0.001)
                                                    ->suffix('DT'),
                                                TextInput::make('consultation_duration')
                                                    ->label('Durée moyenne de consultation')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->suffix('min'),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Compte utilisateur')
                            ->schema([
                                Section::make('Compte utilisateur')
                                    ->description('Lier le médecin à un compte utilisateur existant. Le rôle « Médecin » est attribué automatiquement.')
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Utilisateur')
                                            ->relationship('user', 'name')
                                            ->searchable(['name', 'email'])
                                            ->preload()
                                            ->helperText('Sélectionnez un compte existant ou laissez vide pour créer un médecin non lié à un compte.'),
                                    ]),
                            ]),
                        Tab::make('Documents')
                            ->schema([
                                Section::make('Diplôme')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('diploma')
                                            ->label('Diplôme')
                                            ->collection('diploma')
                                            ->acceptedFileTypes(['application/pdf', 'image/png', 'image/jpeg'])
                                            ->helperText('PDF ou image (PNG/JPG).'),
                                    ]),
                                Section::make('Signature numérique')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('signature')
                                            ->label('Signature')
                                            ->collection('signature')
                                            ->image()
                                            ->helperText('Image PNG avec fond transparent recommandée.'),
                                    ]),
                            ]),
                        Tab::make('Profil')
                            ->schema([
                                Section::make('Biographie')
                                    ->schema([
                                        RichEditor::make('biography')
                                            ->label('Biographie')
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Statut')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Statut')
                                            ->options(DoctorStatus::options())
                                            ->default(DoctorStatus::Active->value)
                                            ->required(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
