<?php

namespace App\Events;

use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class RefundCompleted
{
    use Dispatchable;

    public function __construct(
        public Refund $refund,
        public ?User $actor = null,
    ) {}
}
