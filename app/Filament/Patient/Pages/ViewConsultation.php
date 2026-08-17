<?php

namespace App\Filament\Patient\Pages;

use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\Consultation;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewConsultation extends Page
{
    use HasPatient;
    use InteractsWithInfolists;

    protected string $view = 'filament.patient.pages.view-consultation';

    public ?int $consultationId = null;

    public ?Consultation $consultation = null;

    public function getHeading(): string
    {
        return 'Détail consultation';
    }

    public function mount(int $consultationId): void
    {
        $this->consultationId = $consultationId;

        $patient = $this->getPatient();

        abort_unless($patient, 403);

        $this->consultation = Consultation::query()
            ->where('patient_id', $patient->id)
            ->with(['doctor', 'vitalSign'])
            ->findOrFail($consultationId);
    }

    public static function getRoutePath(Panel $panel): string
    {
        return '/consultation/{consultationId}';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations de la consultation')
                    ->schema([
                        TextEntry::make('consultation_date')
                            ->label('Date')
                            ->date('d/m/Y'),
                        TextEntry::make('doctor.full_name')
                            ->label('Médecin'),
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge(),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),
                    ])
                    ->columns(4),
                Section::make('Motif et symptômes')
                    ->schema([
                        TextEntry::make('reason')
                            ->label('Motif')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('symptoms')
                            ->label('Symptômes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('clinical_examination')
                            ->label('Examen clinique')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Diagnostic')
                    ->schema([
                        TextEntry::make('diagnosis')
                            ->label('Diagnostic principal')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('secondary_diagnoses')
                            ->label('Diagnostics secondaires')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Traitement et recommandations')
                    ->schema([
                        TextEntry::make('treatment_plan')
                            ->label('Plan thérapeutique')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('recommendations')
                            ->label('Recommandations')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Constantes vitales')
                    ->visible(fn (): bool => $this->consultation?->vitalSign !== null)
                    ->schema([
                        TextEntry::make('vitalSign.temperature')
                            ->label('Température')
                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' °C' : '—'),
                        TextEntry::make('vitalSign.weight')
                            ->label('Poids')
                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' kg' : '—'),
                        TextEntry::make('vitalSign.height')
                            ->label('Taille')
                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' cm' : '—'),
                        TextEntry::make('vitalSign.bmi')
                            ->label('IMC')
                            ->placeholder('—'),
                        TextEntry::make('vitalSign.blood_pressure')
                            ->label('Tension artérielle')
                            ->placeholder('—'),
                        TextEntry::make('vitalSign.heart_rate')
                            ->label('Fréquence cardiaque')
                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' bpm' : '—'),
                        TextEntry::make('vitalSign.oxygen_saturation')
                            ->label('Saturation O₂')
                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' %' : '—'),
                        TextEntry::make('vitalSign.respiratory_rate')
                            ->label('Fréquence respiratoire')
                            ->formatStateUsing(fn ($state): string => $state !== null ? $state.' /min' : '—'),
                    ])
                    ->columns(4),
                Section::make('Documents')
                    ->visible(fn (): bool => $this->consultation?->getMedia('consultation_documents')->isNotEmpty())
                    ->schema([
                        RepeatableEntry::make('consultation_documents')
                            ->label('Documents joints')
                            ->state(fn (): array => $this->resolveDocuments())
                            ->schema([
                                TextEntry::make('file_name')
                                    ->label('Fichier'),
                                TextEntry::make('size')
                                    ->label('Taille')
                                    ->formatStateUsing(fn ($state): string => number_format($state / 1024, 0).' Ko'),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    /**
     * @return array<int, array{file_name: string, size: int}>
     */
    private function resolveDocuments(): array
    {
        if (! $this->consultation) {
            return [];
        }

        return $this->consultation
            ->getMedia('consultation_documents')
            ->map(fn ($media): array => [
                'file_name' => $media->file_name,
                'size' => $media->size,
            ])
            ->all();
    }
}
