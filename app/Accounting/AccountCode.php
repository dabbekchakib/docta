<?php

namespace App\Accounting;

/**
 * Codes des comptes du plan comptable (SCF tunisien) utilisés par la
 * génération automatique des écritures.
 */
final class AccountCode
{
    public const CASH = '531';
    public const BANK = '512';
    public const RECEIVABLES = '4111';
    public const REVENUE = '7070';
    public const REVENUE_CONTRA = '7091';
    public const VAT_COLLECTED = '4441';
}
