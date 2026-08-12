<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\LaboratoryRequest;
use App\Models\Patient;
use App\Services\InvoiceService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->prefillFromQuery();
    }

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        return app(InvoiceService::class)->create($data, $items);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    private function prefillFromQuery(): void
    {
        $consultationId = (int) request()->query('consultation');
        $appointmentId = (int) request()->query('appointment');
        $laboratoryRequestId = (int) request()->query('laboratory_request');
        $patientId = (int) request()->query('patient');

        $fill = [];

        if ($consultationId > 0) {
            $consultation = Consultation::query()->find($consultationId);

            if ($consultation) {
                $fill = [
                    'consultation_id' => $consultation->id,
                    'patient_id' => $consultation->patient_id,
                    'doctor_id' => $consultation->doctor_id,
                ];
            }
        } elseif ($appointmentId > 0) {
            $appointment = Appointment::query()->find($appointmentId);

            if ($appointment) {
                $fill = [
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                ];
            }
        } elseif ($laboratoryRequestId > 0) {
            $request = LaboratoryRequest::query()->find($laboratoryRequestId);

            if ($request) {
                $fill = [
                    'laboratory_request_id' => $request->id,
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                ];
            }
        } elseif ($patientId > 0 && Patient::query()->find($patientId)) {
            $fill = ['patient_id' => $patientId];
        }

        if ($fill !== []) {
            $this->form->fill($fill);
        }
    }
}
