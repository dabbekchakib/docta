<?php

namespace App\Filament\Resources\Refunds\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Refunds\Actions\ApproveRefundAction;
use App\Filament\Resources\Refunds\Actions\CancelRefundAction;
use App\Filament\Resources\Refunds\Actions\ExecuteRefundAction;
use App\Filament\Resources\Refunds\Actions\RejectRefundAction;
use App\Filament\Resources\Refunds\RefundResource;
use App\Models\Refund;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ViewRefund extends ViewRecord
{
    protected static string $resource = RefundResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity('refunds')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log('Fiche remboursement consultée');
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
            Action::make('openPayment')
                ->label('Voir le paiement')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('gray')
                ->visible(fn (): bool => $this->record instanceof Refund && $this->record->payment)
                ->url(fn (): string => PaymentResource::getUrl('view', ['record' => $this->record->payment])),
            ApproveRefundAction::make(),
            ExecuteRefundAction::make(),
            RejectRefundAction::make(),
            CancelRefundAction::make(),
        ];
    }
}
