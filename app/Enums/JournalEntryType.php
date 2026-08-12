<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Types d'écriture du journal (saisie manuelle ou génération automatique).
 */
enum JournalEntryType: string implements HasColor, HasLabel
{
    case Manual = 'manual';
    case InvoiceIssue = 'invoice_issue';
    case InvoiceReverse = 'invoice_reverse';
    case Payment = 'payment';
    case CreditNote = 'credit_note';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Écriture manuelle',
            self::InvoiceIssue => 'Émission facture',
            self::InvoiceReverse => 'Annulation facture',
            self::Payment => 'Encaissement',
            self::CreditNote => 'Avoir',
            self::Refund => 'Remboursement',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Manual => 'gray',
            self::InvoiceIssue => 'primary',
            self::InvoiceReverse => 'danger',
            self::Payment => 'success',
            self::CreditNote => 'warning',
            self::Refund => 'info',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }
}
