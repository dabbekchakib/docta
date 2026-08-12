<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class InvoiceIssued
{
    use Dispatchable;

    public function __construct(
        public Invoice $invoice,
        public ?User $actor = null,
    ) {}
}
