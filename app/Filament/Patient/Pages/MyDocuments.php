<?php

namespace App\Filament\Patient\Pages;

use App\Enums\MedicalDocumentType;
use App\Filament\Patient\Pages\Concerns\HasPatient;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MyDocuments extends Page implements HasTable
{
    use HasPatient, InteractsWithTable;

    protected string $view = 'filament.patient.pages.my-documents';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-m-document-duplicate';

    protected static string|\UnitEnum|null $navigationGroup = 'Mes documents';

    protected static ?int $navigationSort = 3;

    public function getHeading(): string
    {
        return 'Mes documents';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getDocumentsQuery())
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('document_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('source_label')
                    ->label('Source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Dossier médical' => 'info',
                        'Consultation' => 'success',
                        'Laboratoire' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'medical' => 'Dossier médical',
                        'consultation' => 'Consultation',
                        'lab' => 'Laboratoire',
                    ])
                    ->query(function (Builder $query, array $state): Builder {
                        return match ($state['value'] ?? null) {
                            'medical' => $query->where('source', 'medical'),
                            'consultation' => $query->where('source', 'consultation'),
                            'lab' => $query->where('source', 'lab'),
                            default => $query,
                        };
                    }),

                SelectFilter::make('document_type')
                    ->label('Type de document')
                    ->options(MedicalDocumentType::options())
                    ->query(function (Builder $query, array $state): Builder {
                        return $state['value']
                            ? $query->where('document_type', $state['value'])
                            : $query;
                    }),
            ])
            ->defaultSort('document_date', 'desc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Aucun document')
            ->emptyStateDescription("Vous n'avez aucun document disponible.");
    }

    private function getDocumentsQuery(): Builder
    {
        $patient = $this->getPatient();

        if (! $patient) {
            return DB::table('medical_documents')
                ->whereRaw('0 = 1')
                ->whereRaw('1 = 0');
        }

        $medicalRecord = $patient->medicalRecord;
        $medicalRecordId = $medicalRecord?->id;

        // Medical documents
        $medicalDocs = DB::table('medical_documents')
            ->select([
                'medical_documents.id',
                'medical_documents.title',
                'medical_documents.document_type',
                'medical_documents.document_date',
                DB::raw("'medical' as source"),
                DB::raw("'Dossier médical' as source_label"),
            ])
            ->where('medical_record_id', $medicalRecordId);

        // Consultation documents (via media table)
        $consultationDocs = DB::table('media')
            ->join('consultations', 'consultations.id', '=', 'media.model_id')
            ->select([
                'media.id',
                DB::raw("COALESCE(media.name, 'Document de consultation') as title"),
                DB::raw("'consultation' as document_type"),
                'consultations.consultation_date as document_date',
                DB::raw("'consultation' as source"),
                DB::raw("'Consultation' as source_label"),
            ])
            ->where('media.model_type', \App\Models\Consultation::class)
            ->where('media.collection_name', 'consultation_documents')
            ->where('consultations.patient_id', $patient->id);

        // Lab reports (via media table)
        $labDocs = DB::table('media')
            ->join('laboratory_reports', 'laboratory_reports.id', '=', 'media.model_id')
            ->join('laboratory_requests', 'laboratory_requests.id', '=', 'laboratory_reports.laboratory_request_id')
            ->select([
                'media.id',
                DB::raw("COALESCE(laboratory_reports.report_number, 'Rapport de laboratoire') as title"),
                DB::raw("'Rapport labo' as document_type"),
                'laboratory_reports.report_date as document_date',
                DB::raw("'lab' as source"),
                DB::raw("'Laboratoire' as source_label"),
            ])
            ->where('media.model_type', \App\Models\LaboratoryReport::class)
            ->where('media.collection_name', 'laboratory_reports')
            ->where('laboratory_requests.patient_id', $patient->id);

        $query = $medicalDocs->unionAll($consultationDocs)->unionAll($labDocs);

        return DB::table(DB::raw("({$query->toSql()}) as documents"))
            ->setBindings($query->getBindings());
    }
}
