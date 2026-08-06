<?php

namespace App\Observers;

use App\Models\Secretary;
use App\Services\SecretaryService;
use Illuminate\Support\Facades\Auth;

class SecretaryObserver
{
    public function __construct(private readonly SecretaryService $service) {}

    public function creating(Secretary $secretary): void
    {
        $secretary->secretary_code ??= $this->service->generateSecretaryCode();
    }

    public function created(Secretary $secretary): void
    {
        $this->service->ensureSecretaryRole($secretary);

        $this->log($secretary, 'Secrétaire créée');
    }

    public function updated(Secretary $secretary): void
    {
        $this->log($secretary, 'Secrétaire modifiée');
    }

    public function deleted(Secretary $secretary): void
    {
        $this->log($secretary, 'Secrétaire supprimée');
    }

    public function restored(Secretary $secretary): void
    {
        $this->log($secretary, 'Secrétaire restaurée');
    }

    public function forceDeleted(Secretary $secretary): void
    {
        $this->log($secretary, 'Secrétaire supprimée définitivement');
    }

    private function log(Secretary $secretary, string $description): void
    {
        activity('secretaries')
            ->performedOn($secretary)
            ->causedBy(Auth::user())
            ->log($description);
    }
}
