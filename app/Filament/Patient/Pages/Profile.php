<?php

namespace App\Filament\Patient\Pages;

use App\Filament\Patient\Pages\Concerns\HasPatient;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Profile extends Page implements HasForms
{
    use HasPatient, InteractsWithForms;

    protected string $view = 'filament.patient.pages.profile';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-m-user';

    protected static ?string $navigationLabel = 'Mon profil';

    protected static string|\UnitEnum|null $navigationGroup = 'Mon compte';

    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public function getHeading(): string
    {
        return 'Mon profil';
    }

    public function mount(): void
    {
        $patient = $this->getPatient();

        if (! $patient) {
            return;
        }

        $this->form->fill([
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'phone' => $patient->phone,
            'email' => $patient->email,
            'address' => $patient->address,
            'city' => $patient->city,
            'governorate' => $patient->governorate?->value,
            'postal_code' => $patient->postal_code,
            'emergency_contact' => $patient->emergency_contact,
            'emergency_relation' => $patient->emergency_relation,
            'emergency_phone' => $patient->emergency_phone,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('profil-tabs')
                    ->schema([
                        Tab::make('Informations personnelles')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('first_name')
                                                    ->label('Prénom')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('last_name')
                                                    ->label('Nom')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('phone')
                                                    ->label('Téléphone')
                                                    ->tel()
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('email')
                                                    ->label('Email')
                                                    ->email()
                                                    ->required()
                                                    ->maxLength(255),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Coordonnées')
                            ->icon('heroicon-m-map-pin')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('address')
                                            ->label('Adresse')
                                            ->maxLength(255),
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('city')
                                                    ->label('Ville')
                                                    ->maxLength(255),
                                                TextInput::make('governorate')
                                                    ->label('Gouvernorat')
                                                    ->maxLength(255),
                                                TextInput::make('postal_code')
                                                    ->label('Code postal')
                                                    ->maxLength(10),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Contact d\'urgence')
                            ->icon('heroicon-m-phone')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('emergency_contact')
                                                    ->label('Nom du contact')
                                                    ->maxLength(255),
                                                TextInput::make('emergency_relation')
                                                    ->label('Lien de parenté')
                                                    ->maxLength(255),
                                                TextInput::make('emergency_phone')
                                                    ->label('Téléphone d\'urgence')
                                                    ->tel()
                                                    ->maxLength(255),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $patient = $this->getPatient();

        if (! $patient) {
            Notification::make()
                ->title('Erreur')
                ->body('Aucun dossier patient trouvé.')
                ->danger()
                ->send();

            return;
        }

        try {
            $data = $this->form->getState();

            $patient->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'governorate' => $data['governorate'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'emergency_relation' => $data['emergency_relation'] ?? null,
                'emergency_phone' => $data['emergency_phone'] ?? null,
            ]);

            Notification::make()
                ->title('Profil mis à jour')
                ->body('Vos informations ont été enregistrées avec succès.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de la mise à jour du profil.')
                ->danger()
                ->send();
        }
    }
}
