<?php

namespace App\Filament\Resources\Prescriptions\Actions;

use App\Filament\Resources\Prescriptions\PrescriptionResource;
use App\Services\PrescriptionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class DuplicatePrescriptionAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'duplicatePrescription')
            ->label('Dupliquer')
            ->icon(Heroicon::OutlinedClipboardDocument)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Dupliquer l\'ordonnance')
            ->modalDescription('Une nouvelle ordonnance brouillon sera créée avec les mêmes médicaments.')
            ->authorize('create')
            ->visible(fn (Action $action): bool => auth()->user()?->can('create', \App\Models\Prescription::class) ?? false)
            ->action(function (Action $action) {
                $copy = app(PrescriptionService::class)->duplicate($action->getRecord());

                Notification::make()
                    ->title('Ordonnance dupliquée')
                    ->success()
                    ->send();

                return PrescriptionResource::getUrl('view', ['record' => $copy]);
            });
    }
}
