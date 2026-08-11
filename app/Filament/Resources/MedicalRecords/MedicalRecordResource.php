<?php

namespace App\Filament\Resources\MedicalRecords;

use App\Filament\Resources\MedicalRecords\Pages\ListMedicalRecords;
use App\Filament\Resources\MedicalRecords\Pages\ViewMedicalRecord;
use App\Filament\Resources\MedicalRecords\RelationManagers\AllergiesRelationManager;
use App\Filament\Resources\MedicalRecords\RelationManagers\ChronicDiseasesRelationManager;
use App\Filament\Resources\MedicalRecords\RelationManagers\FamilyHistoriesRelationManager;
use App\Filament\Resources\MedicalRecords\RelationManagers\LifestyleRelationManager;
use App\Filament\Resources\MedicalRecords\RelationManagers\MedicalDocumentsRelationManager;
use App\Filament\Resources\MedicalRecords\RelationManagers\MedicalHistoriesRelationManager;
use App\Filament\Resources\MedicalRecords\RelationManagers\MedicationsRelationManager;
use App\Filament\Resources\MedicalRecords\RelationManagers\SurgicalHistoriesRelationManager;
use App\Filament\Resources\MedicalRecords\RelationManagers\VaccinationsRelationManager;
use App\Filament\Resources\MedicalRecords\Schemas\MedicalRecordView;
use App\Filament\Resources\MedicalRecords\Tables\MedicalRecordsTable;
use App\Enums\AllergyStatus;
use App\Enums\ChronicDiseaseStatus;
use App\Enums\MedicationStatus;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MedicalRecordResource extends Resource
{
    protected static ?string $model = MedicalRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static string|\UnitEnum|null $navigationGroup = 'Médical';

    protected static ?string $navigationLabel = 'Dossiers médicaux';

    protected static ?string $modelLabel = 'dossier médical';

    protected static ?string $pluralModelLabel = 'dossiers médicaux';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'medical_record_number';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'medical_records.view',
            'medical_records.update',
            'medical_records.manage',
        ]) ?? false;
    }

    /**
     * Les médecins ne voient que les dossiers de leurs patients.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withCount([
                'allergies as critical_allergies_count' => fn (Builder $q) => $q
                    ->where('status', AllergyStatus::Active->value)
                    ->whereIn('severity', ['severe', 'critical']),
                'chronicDiseases as active_chronic_diseases_count' => fn (Builder $q) => $q
                    ->whereIn('status', [ChronicDiseaseStatus::Active->value, ChronicDiseaseStatus::Controlled->value]),
                'medications as active_medications_count' => fn (Builder $q) => $q
                    ->where('status', MedicationStatus::Active->value),
            ]);

        $user = auth()->user();

        if ($user?->hasRole('doctor')) {
            $doctorId = Doctor::query()->where('user_id', $user->id)->value('id');

            if ($doctorId) {
                $query->whereHas('patient', fn (Builder $q) => $q
                    ->whereHas('consultations', fn (Builder $q2) => $q2->where('doctor_id', $doctorId))
                    ->orWhereHas('appointments', fn (Builder $q2) => $q2->where('doctor_id', $doctorId)));
            } else {
                $query->whereKey(-1);
            }
        }

        return $query;
    }

    public static function infolist(Schema $schema): Schema
    {
        return MedicalRecordView::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AllergiesRelationManager::class,
            ChronicDiseasesRelationManager::class,
            MedicalHistoriesRelationManager::class,
            SurgicalHistoriesRelationManager::class,
            FamilyHistoriesRelationManager::class,
            MedicationsRelationManager::class,
            VaccinationsRelationManager::class,
            MedicalDocumentsRelationManager::class,
            LifestyleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicalRecords::route('/'),
            'view' => ViewMedicalRecord::route('/{record}'),
        ];
    }
}
