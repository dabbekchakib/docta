<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\HasLabel;
use Spatie\Activitylog\Models\Activity;

class AppointmentView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('fiche_rendez_vous')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Détails')
                            ->schema([
                                Section::make('Rendez-vous')
                                    ->schema([
                                        TextEntry::make('appointment_number')->label('N° rendez-vous'),
                                        TextEntry::make('patient.full_name')->label('Patient'),
                                        TextEntry::make('doctor.full_name')->label('Médecin'),
                                        TextEntry::make('secretary.full_name')->label('Secrétaire')->placeholder('—'),
                                        TextEntry::make('appointment_date')->label('Date')->date('d/m/Y'),
                                        TextEntry::make('start_time')->label('Heure de début'),
                                        TextEntry::make('end_time')->label('Heure de fin'),
                                        TextEntry::make('duration')->label('Durée')
                                            ->formatStateUsing(fn (?int $state): string => $state ? $state.' min' : '—'),
                                        TextEntry::make('type')->label('Type')
                                            ->formatStateUsing(fn (AppointmentType $state): string => self::enumLabel($state)),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn (AppointmentStatus $state): string => $state->getColor()),
                                        TextEntry::make('reason')->label('Motif')->placeholder('—')->columnSpanFull(),
                                        TextEntry::make('notes')->label('Notes')->placeholder('—')->columnSpanFull(),
                                        TextEntry::make('createdBy.name')->label('Créé par')->placeholder('—'),
                                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                                    ])
                                    ->columns(3),
                                Section::make('Suivi')
                                    ->schema([
                                        TextEntry::make('confirmed_at')->label('Confirmé le')
                                            ->dateTime('d/m/Y H:i')->placeholder('—'),
                                        TextEntry::make('cancelled_at')->label('Annulé le')
                                            ->dateTime('d/m/Y H:i')->placeholder('—'),
                                        TextEntry::make('completed_at')->label('Terminé le')
                                            ->dateTime('d/m/Y H:i')->placeholder('—'),
                                    ])
                                    ->columns(3),
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
        $appointment = $component->getRecord();

        if (! $appointment instanceof Appointment) {
            return [];
        }

        return $appointment->activities()
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
