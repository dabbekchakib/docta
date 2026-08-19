<?php

namespace App\Filament\Patient\Pages;

use App\Enums\LaboratoryRequestPriority;
use App\Enums\LaboratoryRequestStatus;
use App\Filament\Patient\Pages\Concerns\HasPatient;
use App\Models\LaboratoryRequest;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyLabExams extends Page implements HasTable
{
    use HasPatient;
    use InteractsWithTable;

    protected string $view = 'filament.patient.pages.my-lab-exams';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $navigationLabel = 'Mes examens';

    protected static string|\UnitEnum|null $navigationGroup = 'Mes soins';

    protected static ?int $navigationSort = 3;

    public function getHeading(): string
    {
        return 'Mes examens';
    }

    public function table(Table $table): Table
    {
        $patient = $this->getPatient();

        return $table
            ->query(
                LaboratoryRequest::query()
                    ->where('patient_id', $patient?->id ?? 0)
                    ->with(['doctor', 'laboratory'])
                    ->latest('requested_at')
            )
            ->columns([
                TextColumn::make('request_number')
                    ->label('N° demande')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record): string => ViewLabExam::getUrl(['labRequestId' => $record->id])),
                TextColumn::make('requested_at')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('doctor.full_name')
                    ->label('Médecin')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('laboratory.name')
                    ->label('Laboratoire')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('priority')
                    ->label('Priorité')
                    ->badge(),
            ])
            ->defaultSort('requested_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
