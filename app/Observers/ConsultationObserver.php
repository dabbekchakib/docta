<?php

namespace App\Observers;

use App\Enums\ConsultationStatus;
use App\Enums\ConsultationType;
use App\Events\ConsultationCreated;
use App\Models\Consultation;
use App\Services\ConsultationService;
use Illuminate\Support\Facades\Auth;

class ConsultationObserver
{
    public function __construct(private readonly ConsultationService $service) {}

    public function creating(Consultation $consultation): void
    {
        $consultation->consultation_number ??= $this->service->generateConsultationNumber();
        $consultation->status ??= ConsultationStatus::Scheduled;
        $consultation->type ??= ConsultationType::FirstVisit;
        $consultation->consultation_date ??= now()->toDateString();
        $consultation->created_by ??= Auth::id();
    }

    public function created(Consultation $consultation): void
    {
        ConsultationCreated::dispatch($consultation);

        $this->log($consultation, 'Consultation créée');
    }

    public function updated(Consultation $consultation): void
    {
        if ($consultation->wasChanged('diagnosis')) {
            $this->log($consultation, 'Diagnostic modifié');
        }

        $this->log($consultation, 'Consultation modifiée');
    }

    public function deleted(Consultation $consultation): void
    {
        $this->log($consultation, 'Consultation supprimée');
    }

    public function restored(Consultation $consultation): void
    {
        $this->log($consultation, 'Consultation restaurée');
    }

    public function forceDeleted(Consultation $consultation): void
    {
        $this->log($consultation, 'Consultation supprimée définitivement');
    }

    private function log(Consultation $consultation, string $description): void
    {
        activity('consultations')
            ->performedOn($consultation)
            ->causedBy(Auth::user())
            ->log($description);
    }
}
