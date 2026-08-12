<?php

namespace App\Events;

use App\Models\CreditNote;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class CreditNoteIssued
{
    use Dispatchable;

    public function __construct(
        public CreditNote $creditNote,
        public ?User $actor = null,
    ) {}
}
