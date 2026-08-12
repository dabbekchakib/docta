<?php

namespace App\Filament\Resources\LaboratoryRequests\Pages;

use App\Filament\Resources\LaboratoryRequests\LaboratoryRequestResource;
use App\Services\LaboratoryRequestService;
use Filament\Resources\Pages\EditRecord;

class EditLaboratoryRequest extends EditRecord
{
    protected static string $resource = LaboratoryRequestResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        app(LaboratoryRequestService::class)->authorizeDoctorFor((int) ($data['doctor_id'] ?? $this->record->doctor_id), auth()->user());

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
