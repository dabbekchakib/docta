<?php

namespace App\Filament\Resources\Secretaries\Actions;

use App\Filament\Resources\Secretaries\Schemas\SecretaryForm;
use App\Models\Secretary;
use Filament\Actions\CreateAction;
use Filament\Schemas\Schema;

class HeaderAction
{
    public static function make(): CreateAction
    {
        return CreateAction::make()
            ->label('Nouvelle secrétaire')
            ->icon('heroicon-o-user-plus')
            ->model(Secretary::class)
            ->form(fn (Schema $schema): Schema => SecretaryForm::configure($schema))
            ->using(function (array $data): Secretary {
                $userId = $data['user_id'] ?? null;

                unset($data['user_id']);

                $secretary = Secretary::create($data);

                if ($userId) {
                    $secretary->user()->associate($userId);
                    $secretary->save();
                }

                return $secretary;
            });
    }
}
