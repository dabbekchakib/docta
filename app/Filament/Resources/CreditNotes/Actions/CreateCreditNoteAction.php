<?php

namespace App\Filament\Resources\CreditNotes\Actions;

use App\Enums\InvoiceStatus;
use App\Models\CreditNote;
use App\Services\CreditNoteService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class CreateCreditNoteAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'createCreditNote')
            ->label('Créer un avoir')
            ->icon(Heroicon::OutlinedDocumentMinus)
            ->color('warning')
            ->authorize('create', CreditNote::class)
            ->visible(fn (Action $action): bool => self::canCreate($action))
            ->modalHeading('Créer un avoir')
            ->modalDescription('Le montant est plafonné au solde créditable de la facture.')
            ->form([
                TextInput::make('amount')
                    ->label('Montant de l\'avoir (TND)')
                    ->numeric()
                    ->required()
                    ->minValue(0.001)
                    ->prefix('DT')
                    ->default(fn (Action $action): string => ($action->getRecord()?->total ?? 0) > 0
                        ? number_format((float) $action->getRecord()->total, 3, '.', '')
                        : '0.000'),
                DatePicker::make('credit_note_date')
                    ->label('Date de l\'avoir')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->default(now()->toDateString())
                    ->required(),
                Textarea::make('reason')
                    ->label('Motif')
                    ->rows(3)
                    ->required(),
            ])
            ->action(function (Action $action, array $data): void {
                $creditNote = app(CreditNoteService::class)->create($action->getRecord(), $data);

                Notification::make()
                    ->title('Avoir créé')
                    ->body('Avoir n° '.$creditNote->credit_note_number.' (brouillon)')
                    ->success()
                    ->send();
            });
    }

    private static function canCreate(Action $action): bool
    {
        $invoice = $action->getRecord();

        if (! $invoice) {
            return false;
        }

        return in_array($invoice->status, [
            InvoiceStatus::Issued,
            InvoiceStatus::PartiallyPaid,
            InvoiceStatus::Paid,
            InvoiceStatus::Overdue,
        ], true)
            && auth()->user()?->can('create', CreditNote::class);
    }
}
