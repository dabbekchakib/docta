<?php

namespace App\Filament\Resources\Prescriptions\Pages;

use App\Enums\PrescriptionStatus;
use App\Filament\Resources\Prescriptions\PrescriptionResource;
use App\Models\Consultation;
use App\Services\PrescriptionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePrescription extends CreateRecord
{
    protected static string $resource = PrescriptionResource::class;

    public function mount(): void
    {
        parent::mount();

        $consultationId = (int) request()->query('consultation');
        $patientId = (int) request()->query('patient');

        if ($consultationId > 0) {
            $consultation = Consultation::query()->find($consultationId);

            if ($consultation) {
                $this->form->fill([
                    'consultation_id' => $consultation->id,
                    'patient_id' => $consultation->patient_id,
                    'doctor_id' => $consultation->doctor_id,
                ]);
            }

            return;
        }

        if ($patientId > 0) {
            $this->form->fill(['patient_id' => $patientId]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        app(PrescriptionService::class)->authorizeDoctorFor((int) ($data['doctor_id'] ?? 0), auth()->user());

        $data['prescription_number'] = app(PrescriptionService::class)->generateNumber();
        $data['status'] = PrescriptionStatus::Draft->value;
        $data['verification_token'] = Str::random(40);
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
