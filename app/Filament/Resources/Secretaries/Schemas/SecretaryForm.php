<?php

namespace App\Filament\Resources\Secretaries\Schemas;

use App\Enums\Governorate;
use App\Enums\Role;
use App\Enums\SecretaryGender;
use App\Enums\SecretaryStatus;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class SecretaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('secretary')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations personnelles')
                            ->schema([
                                Section::make('Identité')
                                    ->description('Le code secrétaire est généré automatiquement à l\'enregistrement.')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Placeholder::make('secretary_code')
                                                    ->label('Code secrétaire')
                                                    ->content(fn (Placeholder $component): string => $component->getRecord()?->secretary_code ?? 'SEC-xxxxxx (généré automatiquement)'),
                                                Select::make('gender')
                                                    ->label('Sexe')
                                                    ->options(SecretaryGender::options())
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
                                                TextInput::make('cin')
                                                    ->label('CIN')
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true),
                                            ]),
                                    ]),
                                Section::make('Photo')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('photo')
                                            ->label('Photo de la secrétaire')
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
                                Section::make('Emploi')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('employee_number')
                                                    ->label('N° employé')
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true),
                                                DatePicker::make('hire_date')
                                                    ->label('Date d\'embauche')
                                                    ->displayFormat('d/m/Y')
                                                    ->native(false),
                                            ]),
                                    ]),
                                Section::make('Statut')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Statut')
                                            ->options(SecretaryStatus::options())
                                            ->default(SecretaryStatus::Active->value)
                                            ->required(),
                                    ]),
                                Section::make('Notes')
                                    ->schema([
                                        Textarea::make('notes')
                                            ->label('Notes administratives')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Compte utilisateur')
                            ->schema([
                                Section::make('Compte utilisateur')
                                    ->description('Lier la secrétaire à un compte utilisateur. Le rôle « Secrétaire » est attribué automatiquement.')
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Utilisateur')
                                            ->relationship('user', 'name')
                                            ->searchable(['name', 'email'])
                                            ->preload()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label('Nom complet')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('email')
                                                    ->label('Adresse email')
                                                    ->email()
                                                    ->required()
                                                    ->unique('users', 'email')
                                                    ->maxLength(255),
                                                TextInput::make('password')
                                                    ->label('Mot de passe')
                                                    ->password()
                                                    ->revealable()
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->createOptionAction(fn (Action $action) => $action->label('Créer un compte utilisateur'))
                                            ->createOptionUsing(function (array $data, Select $component): int {
                                                $user = User::create([
                                                    'name' => $data['name'],
                                                    'email' => $data['email'],
                                                    'password' => $data['password'],
                                                    'email_verified_at' => now(),
                                                ]);

                                                $user->assignRole(Role::Secretary->value);

                                                return $user->getKey();
                                            })
                                            ->helperText('Sélectionnez un compte existant ou créez-en un nouveau. La désactivation de la secrétaire bloquera également son compte.'),
                                    ]),
                            ]),
                        Tab::make('Documents')
                            ->schema([
                                Section::make('Documents administratifs')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('documents')
                                            ->label('Documents')
                                            ->collection('documents')
                                            ->multiple()
                                            ->acceptedFileTypes(['application/pdf', 'image/png', 'image/jpeg'])
                                            ->helperText('PDF ou image (PNG/JPG).'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
