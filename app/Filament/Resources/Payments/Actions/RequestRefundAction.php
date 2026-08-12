<?php

namespace App\Filament\Resources\Payments\Actions;

use App\Enums\PaymentStatus;
use App\Models\Refund;
use App\Services\RefundService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class RequestRefundAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'requestRefund')
            ->label('Demander un remboursement')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('warning')
            ->authorize('create', Refund::class)
            ->visible(fn (Action $action): bool => self::canRequest($action))
            ->modalHeading('Demande de remboursement')
            ->modalDescription('Le montant est plafonné au solde remboursable de ce paiement.')
            ->form([
                TextInput::make('amount')
                    ->label('Montant à rembourser (TND)')
                    ->numeric()
                    ->required()
                    ->minValue(0.001)
                    ->prefix('DT')
                    ->default(fn (Action $action): string => $action->getRecord()->amount > 0
                        ? number_format((float) $action->getRecord()->amount, 3, '.', '')
                        : '0.000'),
                DatePicker::make('refund_date')
                    ->label('Date du remboursement')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->default(now()->toDateString())
                    ->required(),
                Select::make('refund_method')
                    ->label('Méthode de remboursement')
                    ->options([
                        'cash' => 'Espèces',
                        'bank_transfer' => 'Virement bancaire',
                        'check' => 'Chèque',
                        'card' => 'Carte bancaire',
                    ])
                    ->default('cash')
                    ->native(false)
                    ->required(),
                TextInput::make('reference')
                    ->label('Référence')
                    ->placeholder('N° de chèque, virement…')
                    ->nullable(),
                Textarea::make('reason')
                    ->label('Motif')
                    ->rows(2),
            ])
            ->action(function (Action $action, array $data): void {
                $refund = app(RefundService::class)->request($action->getRecord(), $data);

                Notification::make()
                    ->title('Demande enregistrée')
                    ->body('Remboursement n° '.$refund->refund_number.' en attente d\'approbation.')
                    ->warning()
                    ->send();
            });
    }

    private static function canRequest(Action $action): bool
    {
        $payment = $action->getRecord();

        if (! $payment) {
            return false;
        }

        return $payment->status === PaymentStatus::Completed
            && (float) $payment->amount > 0
            && auth()->user()?->can('create', Refund::class);
    }
}
