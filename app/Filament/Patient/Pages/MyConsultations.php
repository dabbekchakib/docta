<?php

namespace App\Filament\Patient\Pages;

use App\Enums\ConsultationStatus;
use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\Consultation;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MyConsultations extends Page implements HasTable
{
    use HasPatient;
    use InteractsWithTable;

    protected string $view = 'filament.patient.pages.my-consultations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Mes soins';

    protected static ?int $navigationSort = 1;

    public function getHeading(): string
    {
        return 'Mes consultations';
    }

    public function table(Table $table): Table
    {
        $patient = $this->getPatient();

        return $table
            ->query(
                Consultation::query()
                    ->where('patient_id', $patient?->id ?? 0)
                    ->with('doctor')
                    ->latest('consultation_date')
            )
            ->columns([
                TextColumn::make('consultation_number')
                    ->label('N° consultation')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record): string => ViewConsultation::getUrl(['consultationId' => $record->id])),
                TextColumn::make('consultation_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('doctor.full_name')
                    ->label('Médecin')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('diagnosis')
                    ->label('Diagnostic')
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ConsultationStatus::options()),
            ])
            ->defaultSort('consultation_date', 'desc')
            ->paginated([10, 25, 50]);
    }
}
