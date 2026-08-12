<?php

namespace App\Filament\Resources\LaboratoryRequests\Pages;

use App\Filament\Resources\LaboratoryRequests\LaboratoryRequestResource;
use App\Services\LaboratoryResultService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ValidateResults extends ViewRecord
{
    protected static string $resource = LaboratoryRequestResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('laboratory_results')
            ->performedOn($this->record)
            ->causedBy(auth()->user())
            ->log('Page de validation des résultats consultée');
    }

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->can('validateResults', $this->getRecord()) ?? false, 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('validateNow')
                ->label('Valider les résultats')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Validation biologique')
                ->modalDescription('La validation verrouille les résultats et notifie le médecin prescripteur.')
                ->authorize('validateResults')
                ->action(function (Action $action): void {
                    app(LaboratoryResultService::class)->validate($this->record);

                    Notification::make()
                        ->title('Résultats validés')
                        ->body('Le médecin prescripteur a été notifié.')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),
            Action::make('back')
                ->label('Retour')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('view', ['record' => $this->record])),
        ];
    }
}
