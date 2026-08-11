<?php

namespace App\Filament\Resources\MedicalRecords\RelationManagers;

use App\Enums\MedicalDocumentType;
use App\Models\MedicalDocument;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MedicalDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'medicalDocuments';

    protected static ?string $modelLabel = 'document médical';

    protected static ?string $pluralModelLabel = 'documents médicaux';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')->label('Titre')->required()->maxLength(191),
                Select::make('document_type')->label('Type')
                    ->options(MedicalDocumentType::options())
                    ->required(),
                SpatieMediaLibraryFileUpload::make('file')
                    ->label('Fichier')
                    ->collection('medical_documents')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(10240)
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('document_date')->label('Date du document'),
                TextInput::make('issued_by')->label('Émis par')->maxLength(191),
                Checkbox::make('is_confidential')->label('Confidentiel'),
                Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Titre')->searchable()->weight('semibold'),
                TextColumn::make('document_type')->label('Type')->badge(),
                TextColumn::make('document_date')->label('Date')->date('d/m/Y')->placeholder('—'),
                TextColumn::make('issued_by')->label('Émis par')->placeholder('—'),
                IconColumn::make('is_confidential')->label('Confidentiel')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedLockClosed)
                    ->falseIcon(Heroicon::OutlinedLockOpen),
                TextColumn::make('createdBy.name')->label('Ajouté par')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('document_type')->label('Type')->options(MedicalDocumentType::options()),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Télécharger')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('primary')
                    ->url(fn (MedicalDocument $record): string => route('medical-documents.download', $record)),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
