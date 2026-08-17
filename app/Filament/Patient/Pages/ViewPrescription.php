<?php

namespace App\Filament\Patient\Pages;

use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\Prescription;
use App\Services\PrescriptionPdfService;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewPrescription extends Page
{
    use HasPatient;
    use InteractsWithInfolists;

    protected string $view = 'filament.patient.pages.view-prescription';

    public ?int $prescriptionId = null;

    public ?Prescription $prescription = null;

    public function getHeading(): string
    {
        return 'Ordonnance';
    }

    public function mount(int $prescriptionId): void
    {
        $this->prescriptionId = $prescriptionId;

        $patient = $this->getPatient();

        abort_unless($patient, 403);

        $this->prescription = Prescription::query()
            ->where('patient_id', $patient->id)
            ->with(['doctor', 'items'])
            ->findOrFail($prescriptionId);
    }

    public static function getRoutePath(Panel $panel): string
    {
        return '/prescription/{prescriptionId}';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Télécharger PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('primary')
                ->action(fn () => app(PrescriptionPdfService::class)->download($this->prescription)),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations de l\'ordonnance')
                    ->schema([
                        TextEntry::make('prescription_number')
                            ->label('N° ordonnance'),
                        TextEntry::make('prescription_date')
                            ->label('Date')
                            ->date('d/m/Y'),
                        TextEntry::make('doctor.full_name')
                            ->label('Médecin prescripteur'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),
                    ])
                    ->columns(4),
                Section::make('Médicaments prescrits')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Médicaments')
                            ->schema([
                                TextEntry::make('medicine_name')
                                    ->label('Médicament'),
                                TextEntry::make('dosage')
                                    ->label('Dosage'),
                                TextEntry::make('frequency')
                                    ->label('Fréquence'),
                                TextEntry::make('duration')
                                    ->label('Durée')
                                    ->formatStateUsing(function (TextEntry $component, $state): string {
                                        $unit = $component->getRecord()?->duration_unit;

                                        return $state !== null
                                            ? $state.' '.($unit?->getLabel() ?? '')
                                            : '—';
                                    }),
                                TextEntry::make('instructions')
                                    ->label('Instructions')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ])
                            ->columns(5),
                    ]),
                Section::make('Notes')
                    ->visible(fn (): bool => filled($this->prescription?->notes))
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
