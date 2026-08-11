<?php

namespace App\Filament\Resources\Prescriptions\Pages;

use App\Filament\Resources\Prescriptions\PrescriptionResource;
use Filament\Resources\Pages\EditRecord;

class EditPrescription extends EditRecord
{
    protected static string $resource = PrescriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
