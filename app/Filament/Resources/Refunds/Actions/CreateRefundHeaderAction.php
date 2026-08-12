<?php

namespace App\Filament\Resources\Refunds\Actions;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\RefundService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class CreateRefundHeaderAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'createRefundFromList')
            ->label('Demander un remboursement')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('warning')
            ->authorize('create', Refund::class)
            ->modalHeading('Demande de remboursement')
            ->modalDescription('Sélectionnez un paiement encaissé : le montant est plafonné à son solde remboursable.')
            ->form([
                Select::make('payment_id')
                    ->label('Paiement à rembourser')
                    ->searchable()
                    ->preload()
                    ->getSearchResultsUsing(fn (?string $search): array => self::searchPayments($search))
                    ->getOptionLabelUsing(fn (int $value): ?string => self::paymentLabel($value))
                    ->live()
                    ->afterStateUpdated(function (mixed $state, mixed $set): void {
                        $balance = self::refundableBalance((int) ($state ?? 0));

                        if ($balance !== null) {
                            $set('amount', number_format($balance, 3, '.', ''));
                        }
                    })
                    ->required(),
                TextInput::make('amount')
                    ->label('Montant à rembourser (TND)')
                    ->numeric()
                    ->required()
                    ->minValue(0.001)
                    ->step('0.001')
                    ->prefix('DT')
                    ->maxValue(fn (Get $get): ?float => self::refundableBalance((int) ($get('payment_id') ?? 0)))
                    ->helperText(fn (Get $get): string => self::refundableBalanceHelper((int) ($get('payment_id') ?? 0)))
                    ->validationMessages([
                        'max' => 'Le montant ne peut pas dépasser le solde remboursable du paiement.',
                    ]),
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
                    ->nullable()
                    ->maxLength(255),
                Textarea::make('reason')
                    ->label('Motif')
                    ->rows(2),
            ])
            ->action(function (array $data): void {
                $payment = Payment::query()->find((int) $data['payment_id']);

                abort_unless($payment instanceof Payment, 422, 'Paiement introuvable.');

                $refund = app(RefundService::class)->request($payment, $data);

                Notification::make()
                    ->title('Demande enregistrée')
                    ->body('Remboursement n° '.$refund->refund_number.' en attente d\'approbation.')
                    ->warning()
                    ->send();
            });
    }

    /**
     * @return array<int, string>
     */
    private static function searchPayments(?string $search): array
    {
        return self::eligiblePayments($search)
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (Payment $payment): array => [
                $payment->id => self::paymentLabel($payment),
            ])
            ->all();
    }

    private static function paymentLabel(int|Payment $payment): ?string
    {
        $payment = $payment instanceof Payment ? $payment : self::eligiblePayments()->find($payment);

        if (! $payment) {
            return null;
        }

        return $payment->payment_number.' — '.($payment->patient?->full_name ?? '—');
    }

    private static function eligiblePayments(?string $search = null): Builder
    {
        $user = auth()->user();

        return Payment::query()
            ->with('patient', 'invoice')
            ->where('status', PaymentStatus::Completed)
            ->when($search, fn (Builder $query, string $search): Builder => $query
                ->where(fn (Builder $sub) => $sub
                    ->where('payment_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn (Builder $patient) => $patient
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%"))
                ))
            ->when($user?->hasRole('doctor'), function (Builder $query) use ($user): void {
                $doctorId = \App\Models\Doctor::query()->where('user_id', $user->id)->value('id');

                if ($doctorId) {
                    $query->whereHas('invoice', fn (Builder $invoice) => $invoice->where('doctor_id', $doctorId));
                } else {
                    $query->whereKey(-1);
                }
            })
            ->latest('payment_date');
    }

    private static function refundableBalance(?int $paymentId): ?float
    {
        if ($paymentId === null || $paymentId <= 0) {
            return null;
        }

        $payment = Payment::query()->whereKey($paymentId)->first();

        if (! $payment) {
            return null;
        }

        return (float) app(RefundService::class)->refundableBalance($payment);
    }

    private static function refundableBalanceHelper(?int $paymentId): string
    {
        $balance = self::refundableBalance($paymentId);

        if ($balance === null) {
            return 'Sélectionnez un paiement pour afficher le solde remboursable.';
        }

        return 'Solde remboursable : '.number_format($balance, 3, ',', ' ').' DT';
    }
}
