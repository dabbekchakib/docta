<?php

namespace App\Filament\Resources\CreditNotes\Pages;

use App\Filament\Resources\CreditNotes\Actions\CancelCreditNoteAction;
use App\Filament\Resources\CreditNotes\Actions\DownloadCreditNoteAction;
use App\Filament\Resources\CreditNotes\Actions\IssueCreditNoteAction;
use App\Filament\Resources\CreditNotes\CreditNoteResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Payments\Actions\RequestRefundAction;
use App\Models\CreditNote;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ViewCreditNote extends ViewRecord
{
    protected static string $resource = CreditNoteResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('credit_notes')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche avoir consultée');
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.stacked-header', [
            'breadcrumbs' => filament()->hasBreadcrumbs() ? $this->getBreadcrumbs() : [],
            'heading' => $this->getHeading(),
            'subheading' => $this->getSubheading(),
            'actions' => $this->getCachedHeaderActions(),
            'actionsAlignment' => $this->getHeaderActionsAlignment(),
            'scopes' => $this->getRenderHookScopes(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openInvoice')
                ->label('Voir la facture')
                ->icon(Heroicon::OutlinedReceiptPercent)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof CreditNote && $this->record->invoice)
                ->url(fn (): string => InvoiceResource::getUrl('view', ['record' => $this->record->invoice])),
            IssueCreditNoteAction::make(),
            DownloadCreditNoteAction::make(),
            CancelCreditNoteAction::make(),
        ];
    }
}
