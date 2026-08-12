<?php

namespace App\Filament\Resources\LaboratoryRequests\Pages;

use App\Filament\Resources\LaboratoryRequests\LaboratoryRequestResource;
use App\Filament\Resources\LaboratoryRequests\Schemas\LaboratoryResultEntryForm;
use App\Models\LaboratoryRequest;
use App\Services\LaboratoryResultService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EnterResults extends EditRecord
{
    protected static string $resource = LaboratoryRequestResource::class;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->can('enterResults', $this->getRecord()) ?? false, 403);
    }

    public function form(Schema $schema): Schema
    {
        return LaboratoryResultEntryForm::configure($schema);
    }

    protected function fillForm(): void
    {
        $this->form->fill(['results' => $this->buildResultRows()]);
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        $data = $this->form->getState();

        app(LaboratoryResultService::class)->syncResults($this->record, $data['results'] ?? []);

        Notification::make()
            ->title('Résultats enregistrés')
            ->body('La demande peut maintenant être validée biologiquement.')
            ->success()
            ->send();

        $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Enregistrer les résultats');
    }

    /**
     * Pré-remplit une ligne par examen demandé, en reprenant les résultats
     * déjà saisis (re-saisie) ou l'intervalle de référence applicable.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildResultRows(): array
    {
        /** @var LaboratoryRequest $request */
        $request = $this->record;
        $service = app(LaboratoryResultService::class);

        $rows = [];

        foreach ($request->items()->with(['test', 'results'])->get() as $item) {
            $existing = $item->results->first();

            if ($existing) {
                $rows[] = [
                    'laboratory_request_item_id' => $item->id,
                    'parameter_name' => $existing->parameter_name,
                    'value' => $existing->value,
                    'numeric_value' => $existing->numeric_value,
                    'unit' => $existing->unit,
                    'reference_min' => $existing->reference_min,
                    'reference_max' => $existing->reference_max,
                    'reference_text' => $existing->reference_text,
                    'abnormality' => $existing->abnormality?->value ?? 'auto',
                    'comment' => $existing->comment,
                ];

                continue;
            }

            $range = $service->resolveReferenceRange($item->test, $request->patient);

            $rows[] = [
                'laboratory_request_item_id' => $item->id,
                'parameter_name' => $item->test?->name,
                'value' => null,
                'numeric_value' => null,
                'unit' => $range?->unit ?? $item->test?->unit,
                'reference_min' => $range?->min_value,
                'reference_max' => $range?->max_value,
                'reference_text' => $range?->reference_text ?? $item->test?->default_reference_value,
                'abnormality' => 'auto',
                'comment' => null,
            ];
        }

        return $rows;
    }
}
