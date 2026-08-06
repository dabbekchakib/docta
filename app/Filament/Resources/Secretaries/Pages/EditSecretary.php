<?php

namespace App\Filament\Resources\Secretaries\Pages;

use App\Filament\Resources\Secretaries\SecretaryResource;
use Filament\Resources\Pages\EditRecord;

class EditSecretary extends EditRecord
{
    protected static string $resource = SecretaryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
