<?php

namespace App\Filament\Resources\Secretaries\Schemas;

use App\Enums\Governorate;
use App\Enums\SecretaryStatus;
use App\Models\Secretary;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

class SecretaryView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('fiche_secretaire')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations générales')
                            ->schema([
                                Section::make('Identité')
                                    ->schema([
                                        SpatieMediaLibraryImageEntry::make('photo')
                                            ->label('Photo')
                                            ->collection('photo')
                                            ->circular()
                                            ->defaultImageUrl(fn (Model $record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->first_name . ' ' . $record->last_name) . '&size=140&background=6366f1&color=fff&bold=true'),
                                        TextEntry::make('secretary_code')->label('Code secrétaire'),
                                        TextEntry::make('full_name')->label('Nom complet'),
                                        TextEntry::make('gender')->label('Sexe')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('birth_date')->label('Date de naissance')->date('d/m/Y')->placeholder('—'),
                                        TextEntry::make('cin')->label('CIN')->placeholder('—'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn (SecretaryStatus $state): string => $state->getColor()),
                                        TextEntry::make('user.name')->label('Compte utilisateur')->placeholder('Non lié'),
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
                                Section::make('Emploi')
                                    ->schema([
                                        TextEntry::make('employee_number')->label('N° employé')->placeholder('—'),
                                        TextEntry::make('hire_date')->label('Date d\'embauche')->date('d/m/Y')->placeholder('—'),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Compte utilisateur')
                            ->schema([
                                Section::make('Compte utilisateur')
                                    ->schema([
                                        TextEntry::make('user.name')->label('Nom du compte')->placeholder('Non lié'),
                                        TextEntry::make('user.email')->label('Email du compte')->placeholder('—'),
                                        TextEntry::make('user.is_active')
                                            ->label('Compte actif')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state === null ? null : ($state ? 'Actif' : 'Inactif')))
                                            ->badge()
                                            ->color(fn (bool | null $state): string => match ($state) {
                                                true => 'success',
                                                false => 'danger',
                                                null => 'gray',
                                            }),
                                    ])
                                    ->columns(3),
                            ]),
                        Tab::make('Documents')
                            ->schema([
                                Section::make('Documents administratifs')
                                    ->description('La liste des documents sera disponible avec le module Dossier médical (Phase 4).')
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
                                        TextEntry::make('causer.name')->label('Utilisateur'),
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
        $secretary = $component->getRecord();

        if (! $secretary instanceof Secretary) {
            return [];
        }

        return $secretary->activities()
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

    /**
     * @return array<int, Action>
     */
    public static function resolveAdminActions(): array
    {
        return [
            Action::make('toggleStatus')
                ->label(fn (Action $action): string => $action->getRecord()?->status === SecretaryStatus::Active ? 'Désactiver la secrétaire' : 'Activer la secrétaire')
                ->color(fn (Action $action): string => $action->getRecord()?->status === SecretaryStatus::Active ? 'danger' : 'success')
                ->icon(fn (Action $action): string => $action->getRecord()?->status === SecretaryStatus::Active ? 'heroicon-m-lock-closed' : 'heroicon-m-lock-open')
                ->requiresConfirmation()
                ->action(function (Action $action): void {
                    /** @var Model $record */
                    $record = $action->getRecord();

                    $newStatus = $record->status === SecretaryStatus::Active->value
                        ? SecretaryStatus::Inactive
                        : SecretaryStatus::Active;

                    $record->status = $newStatus->value;
                    $record->save();

                    if ($record->user) {
                        $record->user->is_active = $newStatus === SecretaryStatus::Active;
                        $record->user->save();
                    }
                }),
            Action::make('resetPassword')
                ->label('Réinitialiser le mot de passe')
                ->color('warning')
                ->icon('heroicon-m-key')
                ->requiresConfirmation()
                ->form([
                    TextInput::make('new_password')
                        ->label('Nouveau mot de passe')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8),
                ])
                ->action(function (Action $action, array $data): void {
                    /** @var Model $record */
                    $record = $action->getRecord();

                    if (! $record->user) {
                        throw new \RuntimeException('Aucun compte utilisateur lié à cette secrétaire.');
                    }

                    $record->user->password = Hash::make($data['new_password']);
                    $record->user->save();
                }),
            DeleteAction::make(),
        ];
    }
}
