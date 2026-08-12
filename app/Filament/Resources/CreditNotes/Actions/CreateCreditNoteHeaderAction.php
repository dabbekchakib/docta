<?php

namespace App\Filament\Resources\CreditNotes\Actions;

use App\Enums\CreditNoteStatus;
use App\Enums\InvoiceStatus;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Services\CreditNoteService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class CreateCreditNoteHeaderAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'createCreditNoteFromList')
            ->label('Créer un avoir')
            ->icon(Heroicon::OutlinedDocumentMinus)
            ->color('warning')
            ->authorize('create', CreditNote::class)
            ->modalHeading('Créer un avoir')
            ->modalDescription('Sélectionnez une facture émise : le montant est plafonné à son solde créditable.')
            ->form([
                Select::make('invoice_id')
                    ->label('Facture à créditer')
                    ->searchable()
                    ->preload()
                    ->getSearchResultsUsing(fn (?string $search): array => self::searchInvoices($search))
                    ->getOptionLabelUsing(fn (int $value): ?string => self::invoiceLabel($value))
                    ->live()
                    ->afterStateUpdated(function (mixed $state, mixed $set): void {
                        $balance = self::creditBalance((int) ($state ?? 0));

                        if ($balance !== null) {
                            $set('amount', number_format($balance, 3, '.', ''));
                        }
                    })
                    ->required(),
                TextInput::make('amount')
                    ->label('Montant de l\'avoir (TND)')
                    ->numeric()
                    ->required()
                    ->minValue(0.001)
                    ->step('0.001')
                    ->prefix('DT')
                    ->maxValue(fn (Get $get): ?float => self::creditBalance((int) ($get('invoice_id') ?? 0)))
                    ->helperText(fn (Get $get): string => self::creditBalanceHelper((int) ($get('invoice_id') ?? 0)))
                    ->validationMessages([
                        'max' => 'Le montant ne peut pas dépasser le solde créditable de la facture.',
                    ]),
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
            ->action(function (array $data): void {
                $invoice = Invoice::query()->find((int) $data['invoice_id']);

                abort_unless($invoice instanceof Invoice, 422, 'Facture introuvable.');

                $creditNote = app(CreditNoteService::class)->create($invoice, $data);

                Notification::make()
                    ->title('Avoir créé')
                    ->body('Avoir n° '.$creditNote->credit_note_number.' (brouillon)')
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<int, string>
     */
    private static function searchInvoices(?string $search): array
    {
        return self::eligibleInvoices($search)
            ->limit(20)
            ->get()
            ->mapWithKeys(fn (Invoice $invoice): array => [
                $invoice->id => self::invoiceLabel($invoice),
            ])
            ->all();
    }

    private static function invoiceLabel(int|Invoice $invoice): ?string
    {
        $invoice = $invoice instanceof Invoice ? $invoice : self::eligibleInvoices()->find($invoice);

        if (! $invoice) {
            return null;
        }

        return $invoice->invoice_number.' — '.($invoice->patient?->full_name ?? '—');
    }

    private static function eligibleInvoices(?string $search = null): Builder
    {
        $user = auth()->user();

        return Invoice::query()
            ->with('patient')
            ->whereIn('status', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::PartiallyPaid->value,
                InvoiceStatus::Paid->value,
                InvoiceStatus::Overdue->value,
            ])
            ->when($search, fn (Builder $query, string $search): Builder => $query
                ->where(fn (Builder $sub) => $sub
                    ->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn (Builder $patient) => $patient
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%"))
                ))
            ->when($user?->hasRole('doctor'), function (Builder $query) use ($user): void {
                $doctorId = \App\Models\Doctor::query()->where('user_id', $user->id)->value('id');

                if ($doctorId) {
                    $query->where('doctor_id', $doctorId);
                } else {
                    $query->whereKey(-1);
                }
            })
            ->latest('invoice_date');
    }

    private static function creditBalance(?int $invoiceId): ?float
    {
        if ($invoiceId === null || $invoiceId <= 0) {
            return null;
        }

        $invoice = Invoice::query()->whereKey($invoiceId)->first();

        if (! $invoice) {
            return null;
        }

        $credited = CreditNote::query()
            ->where('invoice_id', $invoice->id)
            ->where('status', CreditNoteStatus::Issued)
            ->sum('amount');

        return max((float) $invoice->total - (float) $credited, 0);
    }

    private static function creditBalanceHelper(?int $invoiceId): string
    {
        $balance = self::creditBalance($invoiceId);

        if ($balance === null) {
            return 'Sélectionnez une facture pour afficher le solde créditable.';
        }

        return 'Solde créditable : '.number_format($balance, 3, ',', ' ').' DT';
    }
}
