<?php

namespace App\Filament\Resources\LaboratoryRequests\Pages;

use App\Enums\LaboratoryRequestStatus;
use App\Filament\Resources\LaboratoryRequests\LaboratoryRequestResource;
use App\Models\Consultation;
use App\Services\LaboratoryRequestService;
use Filament\Resources\Pages\CreateRecord;

class CreateLaboratoryRequest extends CreateRecord
{
    protected static string $resource = LaboratoryRequestResource::class;

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
        app(LaboratoryRequestService::class)->authorizeDoctorFor((int) ($data['doctor_id'] ?? 0), auth()->user());

        $data['request_number'] = app(LaboratoryRequestService::class)->generateNumber();
        $data['status'] = LaboratoryRequestStatus::Draft->value;
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
