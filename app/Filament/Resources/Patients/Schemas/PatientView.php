<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Enums\PatientStatus;
use App\Filament\Resources\Consultations\ConsultationResource;
use App\Models\Consultation;
use App\Models\Patient;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\HasLabel;
use Spatie\Activitylog\Models\Activity;

class PatientView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                View::make('filament.infolists.dmp-alerts')
                    ->columnSpanFull()
                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()?->medicalRecord]),
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
                                RepeatableEntry::make('appointments')
                                    ->label('Historique des rendez-vous')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveAppointments($component))
                                    ->schema([
                                        TextEntry::make('appointment_date')->label('Date'),
                                        TextEntry::make('start_time')->label('Heure'),
                                        TextEntry::make('doctor.full_name')->label('Médecin'),
                                        TextEntry::make('type')->label('Type')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn ($state): string => $state->getColor()),
                                    ])
                                    ->columns(3),
                            ]),
                        Tab::make('Consultations')
                            ->schema([
                                RepeatableEntry::make('consultations')
                                    ->label('Historique des consultations')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveConsultations($component))
                                    ->schema([
                                        TextEntry::make('consultation_date')->label('Date'),
                                        TextEntry::make('doctor.full_name')->label('Médecin'),
                                        TextEntry::make('reason')->label('Motif'),
                                        TextEntry::make('diagnosis')->label('Diagnostic'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn ($state): string => $state->getColor()),
                                        TextEntry::make('open')->label('')
                                            ->formatStateUsing(fn (): string => 'Ouvrir')
                                            ->color('primary')
                                            ->url(fn (TextEntry $component): ?string => self::consultationUrl($component)),
                                    ])
                                    ->columns(3),
                            ]),
                        Tab::make('Dossier médical')
                            ->schema([
                                View::make('filament.infolists.dmp-summary')
                                    ->columnSpanFull()
                                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()?->medicalRecord]),
                                View::make('filament.infolists.dmp-timeline')
                                    ->columnSpanFull()
                                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()?->medicalRecord]),
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveAppointments(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->appointments()
            ->with('doctor')
            ->latest('appointment_date')
            ->limit(20)
            ->get()
            ->map(fn ($appointment): array => [
                'appointment_date' => $appointment->appointment_date?->format('d/m/Y') ?? '—',
                'start_time' => $appointment->start_time,
                'doctor.full_name' => $appointment->doctor?->full_name,
                'type' => $appointment->type,
                'status' => $appointment->status,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveConsultations(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->consultations()
            ->with('doctor')
            ->latest('consultation_date')
            ->limit(20)
            ->get()
            ->map(fn (Consultation $consultation): array => [
                'id' => $consultation->id,
                'consultation_date' => $consultation->consultation_date?->format('d/m/Y') ?? '—',
                'doctor.full_name' => $consultation->doctor?->full_name,
                'reason' => $consultation->reason,
                'diagnosis' => $consultation->diagnosis ? strip_tags((string) $consultation->diagnosis) : '—',
                'status' => $consultation->status,
            ])
            ->all();
    }

    private static function consultationUrl(TextEntry $component): ?string
    {
        $item = $component->getRecord();

        if (! is_array($item) || empty($item['id'])) {
            return null;
        }

        return ConsultationResource::getUrl('view', ['record' => $item['id']]);
    }
}
