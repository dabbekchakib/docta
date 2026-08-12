<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\CreditNotes\Actions\CreateCreditNoteAction;
use App\Filament\Resources\Invoices\Actions\CancelInvoiceAction;
use App\Filament\Resources\Invoices\Actions\DownloadInvoiceAction;
use App\Filament\Resources\Invoices\Actions\IssueInvoiceAction;
use App\Filament\Resources\Invoices\Actions\RecordPaymentAction;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Models\Invoice;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('invoices')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche facture consultée');
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
            Action::make('openPatient')
                ->label('Fiche patient')
                ->icon(Heroicon::OutlinedUser)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof Invoice
                    && $this->record->patient
                    && (auth()->user()?->can('view', $this->record->patient) ?? false))
                ->url(fn (): string => PatientResource::getUrl('view', ['record' => $this->record->patient])),
            IssueInvoiceAction::make(),
            RecordPaymentAction::make(),
            CreateCreditNoteAction::make(),
            DownloadInvoiceAction::make(),
            CancelInvoiceAction::make(),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
