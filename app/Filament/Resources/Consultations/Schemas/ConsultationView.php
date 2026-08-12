<?php

namespace App\Filament\Resources\Consultations\Schemas;

use App\Enums\ConsultationStatus;
use App\Enums\ConsultationType;
use App\Enums\LaboratoryRequestStatus;
use App\Filament\Resources\LaboratoryRequests\LaboratoryRequestResource;
use App\Models\Consultation;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\HasLabel;
use Spatie\Activitylog\Models\Activity;

class ConsultationView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                View::make('filament.infolists.dmp-alerts')
                    ->columnSpanFull()
                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()?->patient?->medicalRecord]),
                Tabs::make('fiche_consultation')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Patient')
                            ->schema([
                                Section::make('Informations du patient')
                                    ->schema([
                                        TextEntry::make('patient.full_name')->label('Patient'),
                                        TextEntry::make('patient.patient_number')->label('N° dossier'),
                                        TextEntry::make('patient.age')->label('Âge')
                                            ->formatStateUsing(fn (?int $state): string => $state ? $state.' ans' : '—'),
                                        TextEntry::make('patient.gender')->label('Sexe')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('patient.blood_group')->label('Groupe sanguin')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('patient.phone')->label('Téléphone')->placeholder('—'),
                                        TextEntry::make('patient.allergies')->label('Allergies')->placeholder('Aucune allergie connue')->columnSpanFull(),
                                        TextEntry::make('patient.medical_history')->label('Antécédents')->placeholder('Aucun antécédent renseigné')->columnSpanFull(),
                                        TextEntry::make('patient.chronic_diseases')->label('Maladies chroniques')->placeholder('Aucune maladie chronique')->columnSpanFull(),
                                    ])
                                    ->columns(3),
                            ]),
                        Tab::make('Consultation')
                            ->schema([
                                Section::make('Consultation')
                                    ->schema([
                                        TextEntry::make('consultation_number')->label('N° consultation'),
                                        TextEntry::make('consultation_date')->label('Date')->date('d/m/Y'),
                                        TextEntry::make('start_time')->label('Début')->placeholder('—'),
                                        TextEntry::make('end_time')->label('Fin')->placeholder('—'),
                                        TextEntry::make('type')->label('Type')
                                            ->badge()
                                            ->color(fn (ConsultationType $state): string => $state->getColor()),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn (ConsultationStatus $state): string => $state->getColor()),
                                        TextEntry::make('doctor.full_name')->label('Médecin'),
                                        TextEntry::make('appointment.appointment_number')->label('Rendez-vous lié')->placeholder('—'),
                                        TextEntry::make('createdBy.name')->label('Créé par')->placeholder('—'),
                                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                                        TextEntry::make('reason')->label('Motif')->placeholder('—')->columnSpanFull(),
                                        TextEntry::make('symptoms')->label('Symptômes')->placeholder('—')->columnSpanFull(),
                                        TextEntry::make('clinical_examination')->label('Observations cliniques')
                                            ->html()
                                            ->placeholder('—')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(3),
                            ]),
                        Tab::make('Constantes vitales')
                            ->schema([
                                Section::make('Constantes vitales')
                                    ->schema([
                                        TextEntry::make('vitalSign.temperature')->label('Température')
                                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' °C' : '—'),
                                        TextEntry::make('vitalSign.weight')->label('Poids')
                                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' kg' : '—'),
                                        TextEntry::make('vitalSign.height')->label('Taille')
                                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' cm' : '—'),
                                        TextEntry::make('vitalSign.bmi')->label('IMC (kg/m²)')->placeholder('—'),
                                        TextEntry::make('vitalSign.blood_pressure')->label('Tension artérielle')->placeholder('—'),
                                        TextEntry::make('vitalSign.heart_rate')->label('Fréquence cardiaque')
                                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' bpm' : '—'),
                                        TextEntry::make('vitalSign.oxygen_saturation')->label('Saturation O₂')
                                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' %' : '—'),
                                        TextEntry::make('vitalSign.respiratory_rate')->label('Fréquence respiratoire')
                                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' /min' : '—'),
                                    ])
                                    ->columns(4),
                            ]),
                        Tab::make('Diagnostic')
                            ->schema([
                                Section::make('Diagnostic')
                                    ->schema([
                                        TextEntry::make('diagnosis')->label('Diagnostic principal')->html()->placeholder('—')->columnSpanFull(),
                                        TextEntry::make('secondary_diagnoses')->label('Diagnostics secondaires')->placeholder('—')->columnSpanFull(),
                                        TextEntry::make('medical_notes')->label('Notes médicales')->html()->placeholder('—')->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Traitement')
                            ->schema([
                                Section::make('Traitement et suivi')
                                    ->schema([
                                        TextEntry::make('treatment_plan')->label('Plan thérapeutique')->html()->placeholder('—')->columnSpanFull(),
                                        TextEntry::make('recommendations')->label('Recommandations')->html()->placeholder('—')->columnSpanFull(),
                                        TextEntry::make('follow_up_date')->label('Prochain contrôle')->date('d/m/Y')->placeholder('—'),
                                    ]),
                            ]),
                        Tab::make('Examens biologiques')
                            ->schema([
                                RepeatableEntry::make('laboratory_requests')
                                    ->label('Demandes d\'examens')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveLaboratoryRequests($component))
                                    ->schema([
                                        TextEntry::make('request_number')->label('N° demande'),
                                        TextEntry::make('requested_at')->label('Date'),
                                        TextEntry::make('tests')->label('Examens'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn ($state): string => self::requestStatusColor($state)),
                                        TextEntry::make('open')->label('')
                                            ->formatStateUsing(fn (): string => 'Ouvrir')
                                            ->color('primary')
                                            ->url(fn (TextEntry $component): ?string => self::laboratoryRequestUrl($component)),
                                    ])
                                    ->columns(4),
                            ]),
                        Tab::make('Dossier médical')
                            ->schema([
                                View::make('filament.infolists.dmp-summary')
                                    ->columnSpanFull()
                                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()?->patient?->medicalRecord]),
                            ]),
                        Tab::make('Documents')
                            ->schema([
                                Section::make('Documents médicaux')
                                    ->schema([
                                        SpatieMediaLibraryImageEntry::make('consultation_documents')
                                            ->label('Documents')
                                            ->collection('consultation_documents')
                                            ->columnSpanFull(),
                                        TextEntry::make('documents_list')
                                            ->label('Fichiers joints')
                                            ->state(fn (TextEntry $component): string => self::resolveDocuments($component))
                                            ->columnSpanFull(),
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
    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveLaboratoryRequests(RepeatableEntry $component): array
    {
        $consultation = $component->getRecord();

        if (! $consultation instanceof Consultation) {
            return [];
        }

        return $consultation->laboratoryRequests()
            ->with('items.test')
            ->latest('requested_at')
            ->limit(20)
            ->get()
            ->map(fn ($request): array => [
                'id' => $request->id,
                'request_number' => $request->request_number,
                'requested_at' => $request->requested_at?->format('d/m/Y') ?? '—',
                'tests' => $request->items->pluck('test.name')->filter()->implode(', ') ?: '—',
                'status' => $request->status,
            ])
            ->all();
    }

    private static function requestStatusColor(mixed $state): string
    {
        if ($state instanceof LaboratoryRequestStatus) {
            return $state->getColor();
        }

        $status = is_string($state) ? LaboratoryRequestStatus::tryFrom($state) : null;

        return $status?->getColor() ?? 'gray';
    }

    private static function laboratoryRequestUrl(TextEntry $component): ?string
    {
        $item = $component->getRecord();

        if (! is_array($item) || empty($item['id'])) {
            return null;
        }

        return LaboratoryRequestResource::getUrl('view', ['record' => $item['id']]);
    }

    private static function resolveActivities(RepeatableEntry $component): array
    {
        $consultation = $component->getRecord();

        if (! $consultation instanceof Consultation) {
            return [];
        }

        return $consultation->activities()
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

    private static function resolveDocuments(TextEntry $component): string
    {
        $consultation = $component->getRecord();

        if (! $consultation instanceof Consultation) {
            return 'Aucun document joint';
        }

        $media = $consultation->getMedia('consultation_documents');

        if ($media->isEmpty()) {
            return 'Aucun document joint';
        }

        return $media->map(fn ($item): string => $item->file_name.' ('.number_format($item->size / 1024, 0).' Ko)')->implode("\n");
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
