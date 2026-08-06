<?php

namespace App\Filament\Resources\Secretaries\Pages;

use App\Filament\Resources\Secretaries\SecretaryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSecretary extends CreateRecord
{
    protected static string $resource = SecretaryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
