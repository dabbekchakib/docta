<?php

namespace App\Observers;

use App\Models\MedicalRecord;
use App\Services\MedicalRecordService;
use Illuminate\Support\Facades\Auth;

class MedicalRecordObserver
{
    public function __construct(private readonly MedicalRecordService $service) {}

    public function creating(MedicalRecord $record): void
    {
        $record->medical_record_number ??= $this->service->generateMedicalRecordNumber();
    }

    public function created(MedicalRecord $record): void
    {
        activity('medical_records')
            ->performedOn($record)
            ->causedBy(Auth::user())
            ->withProperties(['medical_record_number' => $record->medical_record_number])
            ->log('Dossier médical créé');
    }

    public function updated(MedicalRecord $record): void
    {
        activity('medical_records')
            ->performedOn($record)
            ->causedBy(Auth::user())
            ->withProperties([
                'attributes' => $record->getChanges(),
                'old' => collect($record->getChanges())->mapWithKeys(fn ($value, string $key): array => [$key => $record->getOriginal($key)])->all(),
            ])
            ->log('Dossier médical modifié');
    }

    public function deleted(MedicalRecord $record): void
    {
        $this->log($record, 'Dossier médical supprimé');
    }

    public function restored(MedicalRecord $record): void
    {
        $this->log($record, 'Dossier médical restauré');
    }

    public function forceDeleted(MedicalRecord $record): void
    {
        $this->log($record, 'Dossier médical supprimé définitivement');
    }

    private function log(MedicalRecord $record, string $description): void
    {
        activity('medical_records')
            ->performedOn($record)
            ->causedBy(Auth::user())
            ->log($description);
    }
}
