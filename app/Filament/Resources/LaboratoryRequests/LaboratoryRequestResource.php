<?php

namespace App\Filament\Resources\LaboratoryRequests;

use App\Filament\Resources\LaboratoryRequests\Pages\CreateLaboratoryRequest;
use App\Filament\Resources\LaboratoryRequests\Pages\EditLaboratoryRequest;
use App\Filament\Resources\LaboratoryRequests\Pages\EnterResults;
use App\Filament\Resources\LaboratoryRequests\Pages\ListLaboratoryRequests;
use App\Filament\Resources\LaboratoryRequests\Pages\ValidateResults;
use App\Filament\Resources\LaboratoryRequests\Pages\ViewLaboratoryRequest;
use App\Filament\Resources\LaboratoryRequests\Schemas\LaboratoryRequestForm;
use App\Filament\Resources\LaboratoryRequests\Schemas\LaboratoryRequestView;
use App\Filament\Resources\LaboratoryRequests\Tables\LaboratoryRequestsTable;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LaboratoryRequestResource extends Resource
{
    protected static ?string $model = LaboratoryRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Médical';

    protected static ?string $navigationLabel = 'Examens biologiques';

    protected static ?string $modelLabel = 'demande d\'examen';

    protected static ?string $pluralModelLabel = 'demandes d\'examens';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'request_number';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'laboratory_requests.view',
            'laboratory_requests.create',
            'laboratory_requests.update',
            'laboratory_requests.cancel',
        ]) ?? false;
    }

    /**
     * Les médecins ne voient que leurs propres demandes d'examens.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user?->hasRole('doctor')) {
            $doctorId = Doctor::query()->where('user_id', $user->id)->value('id');

            if ($doctorId) {
                $query->where('doctor_id', $doctorId);
            } else {
                $query->whereKey(-1);
            }
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return LaboratoryRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LaboratoryRequestView::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaboratoryRequestsTable::configure($table);
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
            'index' => ListLaboratoryRequests::route('/'),
            'create' => CreateLaboratoryRequest::route('/create'),
            'view' => ViewLaboratoryRequest::route('/{record}'),
            'edit' => EditLaboratoryRequest::route('/{record}/edit'),
            'results' => EnterResults::route('/{record}/resultats'),
            'validate-results' => ValidateResults::route('/{record}/valider-resultats'),
        ];
    }
}
