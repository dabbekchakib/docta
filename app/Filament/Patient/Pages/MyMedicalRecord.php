<?php

namespace App\Filament\Patient\Pages;

use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\MedicalRecord;
use BackedEnum;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;

class MyMedicalRecord extends Page implements HasInfolists
{
    use HasPatient, InteractsWithInfolists;

    protected string $view = 'filament.patient.pages.my-medical-record';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-m-folder';

    protected static string|\UnitEnum|null $navigationGroup = 'Mes soins';

    protected static ?int $navigationSort = 1;

    protected ?MedicalRecord $medicalRecord;

    public function getHeading(): string
    {
        return 'Mon dossier médical';
    }

    public function mount(): void
    {
        $patient = $this->getPatient();

        if (! $patient) {
            return;
        }

        $this->medicalRecord = $patient->medicalRecord;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $patient = $this->getPatient();
        $record = $this->medicalRecord;

        return $infolist
            ->schema([
                Section::make('Informations générales')
                    ->icon('heroicon-m-heart')
                    ->schema([
                        TextEntry::make('blood_group')
                            ->label('Groupe sanguin')
                            ->placeholder('Non renseigné')
                            ->badge(),
                    ])
                    ->columns(3),

                Section::make('Antécédents médicaux')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        TextEntry::make('medical_histories')
                            ->label('Historique médical')
                            ->state(fn () => $record?->medicalHistories
                                ->map(fn ($h) => "{$h->title} ({$h->type->label()} — {$h->status->label()})")
                                ->implode(', ') ?? 'Aucun antécédent')
                            ->placeholder('Aucun antécédent'),
                    ]),

                Section::make('Allergies')
                    ->icon('heroicon-m-exclamation-triangle')
                    ->schema([
                        TextEntry::make('allergies')
                            ->label('Allergies déclarées')
                            ->state(fn () => $record?->allergies
                                ->map(fn ($a) => "{$a->allergen} ({$a->severity->label()} — {$a->status->label()})")
                                ->implode(', ') ?? 'Aucune allergie déclarée')
                            ->placeholder('Aucune allergie déclarée'),
                    ]),

                Section::make('Maladies chroniques')
                    ->icon('heroicon-m-clipboard')
                    ->schema([
                        TextEntry::make('chronic_diseases')
                            ->label('Maladies chroniques')
                            ->state(fn () => $record?->chronicDiseases
                                ->map(fn ($d) => "{$d->disease_name} ({$d->status->label()})")
                                ->implode(', ') ?? 'Aucune maladie chronique')
                            ->placeholder('Aucune maladie chronique'),
                    ]),

                Section::make('Traitements permanents')
                    ->icon('heroicon-m-beaker')
                    ->schema([
                        TextEntry::make('permanent_treatments')
                            ->label('Traitements en cours')
                            ->state(fn () => $record?->medications
                                ->where('status.value', 'active')
                                ->map(fn ($m) => "{$m->name} — {$m->dosage} ({$m->frequency})")
                                ->implode(', ') ?? ($patient?->permanent_treatments ?? 'Aucun traitement'))
                            ->placeholder('Aucun traitement'),
                    ]),

                Section::make('Notes médicales')
                    ->icon('heroicon-m-pencil')
                    ->schema([
                        TextEntry::make('general_notes')
                            ->label('Notes du dossier')
                            ->state(fn () => $record?->general_notes ?? $patient?->medical_notes ?? 'Aucune note')
                            ->placeholder('Aucune note'),
                    ]),
            ]);
    }
}
