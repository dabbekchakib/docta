<?php

namespace App\Filament\Resources\LaboratoryRequests\Schemas;

use App\Enums\ResultAbnormality;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryTest;
use App\Models\ReferenceRange;
use App\Services\LaboratoryResultService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LaboratoryResultEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Saisie des résultats')
                    ->description('Les valeurs hors intervalle de référence sont signalées automatiquement. Aucun diagnostic n\'est déduit.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('results')
                            ->label('Paramètres et résultats')
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->collapsible()
                            ->addActionLabel('Ajouter un paramètre')
                            ->schema(self::rowSchema($schema))
                            ->columns(4),
                    ]),
            ]);
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private static function rowSchema(Schema $schema): array
    {
        return [
            Select::make('laboratory_request_item_id')
                ->label('Examen')
                ->options(fn (): array => self::itemOptions($schema))
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, $set): void {
                    $test = self::testForItem($state);
                    $set('parameter_name', $test?->name);

                    $range = self::resolveRangeFor($schema, $test);

                    $set('unit', $range?->unit ?? $test?->unit);
                    $set('reference_min', $range?->min_value);
                    $set('reference_max', $range?->max_value);
                    $set('reference_text', $range?->reference_text ?? $test?->default_reference_value);
                }),
            TextInput::make('parameter_name')
                ->label('Paramètre')
                ->required()
                ->maxLength(255),
            TextInput::make('value')
                ->label('Valeur')
                ->required()
                ->maxLength(255),
            TextInput::make('numeric_value')
                ->label('Valeur numérique')
                ->numeric()
                ->step('any')
                ->helperText('Saisie pour comparer à l\'intervalle de référence.'),
            TextInput::make('unit')
                ->label('Unité')
                ->maxLength(50),
            TextInput::make('reference_min')
                ->label('Réf. min')
                ->numeric()
                ->step('any'),
            TextInput::make('reference_max')
                ->label('Réf. max')
                ->numeric()
                ->step('any'),
            TextInput::make('reference_text')
                ->label('Référence (texte)')
                ->placeholder('Ex. : Négatif')
                ->maxLength(255),
            Select::make('abnormality')
                ->label('Anomalie')
                ->options(['auto' => 'Automatique', ...ResultAbnormality::options()])
                ->default('auto')
                ->native(false),
            Textarea::make('comment')
                ->label('Commentaire')
                ->rows(2)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function itemOptions(Schema $schema): array
    {
        $request = self::record($schema);

        if (! $request) {
            return [];
        }

        return $request->items()
            ->with('test')
            ->get()
            ->mapWithKeys(fn ($item): array => [
                $item->id => $item->test?->name ?? "Examen #{$item->id}",
            ])
            ->all();
    }

    private static function testForItem(?int $itemId): ?LaboratoryTest
    {
        if (! $itemId) {
            return null;
        }

        $item = LaboratoryRequestItem::query()->find($itemId);

        return $item?->test;
    }

    private static function resolveRangeFor(Schema $schema, ?LaboratoryTest $test): ?ReferenceRange
    {
        $patient = self::record($schema)?->patient;

        return app(LaboratoryResultService::class)->resolveReferenceRange($test, $patient);
    }

    private static function record(Schema $schema): ?LaboratoryRequest
    {
        $livewire = $schema->getLivewire();

        if ($livewire && method_exists($livewire, 'getRecord')) {
            $record = $livewire->getRecord();

            if ($record instanceof LaboratoryRequest) {
                return $record;
            }
        }

        return null;
    }
}
