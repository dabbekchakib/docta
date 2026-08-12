<?php

namespace App\Events;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentCompleted
{
    use Dispatchable;

    public function __construct(
        public Payment $payment,
        public ?User $actor = null,
    ) {}
}
