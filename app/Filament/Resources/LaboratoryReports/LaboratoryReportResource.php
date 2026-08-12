<?php

namespace App\Filament\Resources\LaboratoryReports;

use App\Filament\Resources\LaboratoryReports\Pages\ListLaboratoryReports;
use App\Filament\Resources\LaboratoryReports\Pages\ViewLaboratoryReport;
use App\Filament\Resources\LaboratoryReports\Schemas\LaboratoryReportView;
use App\Filament\Resources\LaboratoryReports\Tables\LaboratoryReportsTable;
use App\Models\Doctor;
use App\Models\LaboratoryReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LaboratoryReportResource extends Resource
{
    protected static ?string $model = LaboratoryReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Médical';

    protected static ?string $navigationLabel = 'Comptes rendus';

    protected static ?string $modelLabel = 'compte rendu de laboratoire';

    protected static ?string $pluralModelLabel = 'comptes rendus de laboratoire';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'report_number';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'laboratory_reports.view',
            'laboratory_reports.create',
            'laboratory_reports.download',
        ]) ?? false;
    }

    /**
     * Les médecins ne voient que les comptes rendus de leurs propres demandes.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user?->hasRole('doctor')) {
            $doctorId = Doctor::query()->where('user_id', $user->id)->value('id');

            if ($doctorId) {
                $query->whereHas('request', fn (Builder $query): Builder => $query->where('doctor_id', $doctorId));
            } else {
                $query->whereKey(-1);
            }
        }

        return $query;
    }

    public static function infolist(Schema $schema): Schema
    {
        return LaboratoryReportView::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaboratoryReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLaboratoryReports::route('/'),
            'view' => ViewLaboratoryReport::route('/{record}'),
        ];
    }
}
