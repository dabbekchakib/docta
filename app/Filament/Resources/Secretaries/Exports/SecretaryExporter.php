<?php

namespace App\Filament\Resources\Secretaries\Exports;

use App\Enums\Governorate;
use App\Enums\SecretaryGender;
use App\Enums\SecretaryStatus;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\Exporter;

class SecretaryExporter extends Exporter
{
    protected static ?string $model = \App\Models\Secretary::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('secretary_code')
                ->label('Code'),
            ExportColumn::make('last_name')
                ->label('Nom'),
            ExportColumn::make('first_name')
                ->label('Prénom'),
            ExportColumn::make('gender')
                ->label('Sexe')
                ->formatStateUsing(fn (string $state): string => SecretaryGender::from($state)->getLabel()),
            ExportColumn::make('birth_date')
                ->label('Date de naissance'),
            ExportColumn::make('cin')
                ->label('CIN'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('phone')
                ->label('Téléphone'),
            ExportColumn::make('mobile')
                ->label('Mobile'),
            ExportColumn::make('address')
                ->label('Adresse'),
            ExportColumn::make('city')
                ->label('Ville'),
            ExportColumn::make('governorate')
                ->label('Gouvernorat')
                ->formatStateUsing(fn (string $state): string => Governorate::from($state)->getLabel()),
            ExportColumn::make('postal_code')
                ->label('Code postal'),
            ExportColumn::make('employee_number')
                ->label('N° employé'),
            ExportColumn::make('hire_date')
                ->label('Date d\'embauche'),
            ExportColumn::make('status')
                ->label('Statut')
                ->formatStateUsing(fn (string $state): string => SecretaryStatus::from($state)->getLabel()),
            ExportColumn::make('user.email')
                ->label('Compte utilisateur'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'L\'export des secrétaires est terminé.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= " {$failedRowsCount} lignes ont échoué.";
        }

        return $body;
    }
}
