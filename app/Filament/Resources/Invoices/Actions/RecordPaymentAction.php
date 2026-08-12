<?php

namespace App\Filament\Resources\Invoices\Actions;

use App\Models\PaymentMethod;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class RecordPaymentAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'recordPayment')
            ->label('Encaisser')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('primary')
            ->authorize('create', \App\Models\Payment::class)
            ->visible(fn (Action $action): bool => self::canRecord($action))
            ->modalHeading('Encaisser un paiement')
            ->modalDescription('Un reçu (REC-…) sera automatiquement émis. Le montant est plafonné au restant dû.')
            ->form([
                TextInput::make('amount')
                    ->label('Montant (TND)')
                    ->numeric()
                    ->required()
                    ->minValue(0.001)
                    ->prefix('DT')
                    ->default(fn (Action $action): ?string => $action->getRecord()->amount_remaining > 0
                        ? number_format((float) $action->getRecord()->amount_remaining, 3, '.', '')
                        : null)
                    ->helperText(fn (Action $action): string => 'Restant dû : '.number_format((float) $action->getRecord()->amount_remaining, 3, ',', ' ').' DT'),
                Select::make('payment_method_id')
                    ->label('Moyen de paiement')
                    ->options(fn (): array => self::paymentMethods())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
                DatePicker::make('payment_date')
                    ->label('Date du paiement')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->default(now()->toDateString())
                    ->required(),
                TextInput::make('reference')
                    ->label('Référence')
                    ->placeholder('N° de chèque, de carte, virement…')
                    ->nullable(),
                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(2),
            ])
            ->action(function (Action $action, array $data): void {
                $payment = app(PaymentService::class)->record([
                    'invoice_id' => $action->getRecord()->id,
                    'amount' => $data['amount'],
                    'payment_method_id' => $data['payment_method_id'] ?? null,
                    'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                    'reference' => $data['reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                Notification::make()
                    ->title('Paiement enregistré')
                    ->body('Reçu n° '.$payment->receipt?->receipt_number)
                    ->success()
                    ->send();
            });
    }

    private static function canRecord(Action $action): bool
    {
        $invoice = $action->getRecord();

        if (! $invoice) {
            return false;
        }

        return $invoice->isIssued()
            && (float) $invoice->amount_remaining > 0
            && auth()->user()?->can('create', \App\Models\Payment::class);
    }

    /**
     * @return array<int, string>
     */
    private static function paymentMethods(): array
    {
        return PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (PaymentMethod $method): array => [
                $method->id => Str::upper($method->name),
            ])
            ->all();
    }
}
