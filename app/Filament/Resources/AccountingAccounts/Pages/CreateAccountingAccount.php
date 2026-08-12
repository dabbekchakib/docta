<?php

namespace App\Filament\Resources\AccountingAccounts\Pages;

use App\Filament\Resources\AccountingAccounts\AccountingAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountingAccount extends CreateRecord
{
    protected static string $resource = AccountingAccountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
