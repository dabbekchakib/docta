<?php

namespace App\Filament\Resources\Consultations;

use App\Filament\Resources\Consultations\Pages\CreateConsultation;
use App\Filament\Resources\Consultations\Pages\EditConsultation;
use App\Filament\Resources\Consultations\Pages\ListConsultations;
use App\Filament\Resources\Consultations\Pages\ViewConsultation;
use App\Filament\Resources\Consultations\Schemas\ConsultationForm;
use App\Filament\Resources\Consultations\Schemas\ConsultationView;
use App\Filament\Resources\Consultations\Tables\ConsultationsTable;
use App\Models\Consultation;
use App\Models\Doctor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConsultationResource extends Resource
{
    protected static ?string $model = Consultation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Médical';

    protected static ?string $navigationLabel = 'Consultations';

    protected static ?string $modelLabel = 'consultation';

    protected static ?string $pluralModelLabel = 'consultations';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'consultation_number';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission([
            'consultations.view',
            'consultations.create',
            'consultations.update',
            'consultations.delete',
            'consultations.manage',
        ]) ?? false;
    }

    /**
     * Les médecins ne voient que leurs propres consultations.
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
        return ConsultationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConsultationView::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConsultationsTable::configure($table);
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
            'index' => ListConsultations::route('/'),
            'create' => CreateConsultation::route('/create'),
            'view' => ViewConsultation::route('/{record}'),
            'edit' => EditConsultation::route('/{record}/edit'),
        ];
    }
}
