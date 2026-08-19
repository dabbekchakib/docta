<?php

namespace App\Filament\Patient\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyNotifications extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.patient.pages.my-notifications';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-m-bell';

    protected static ?string $navigationLabel = 'Mes notifications';

    protected static string|\UnitEnum|null $navigationGroup = 'Mon compte';

    protected static ?int $navigationSort = 10;

    public function getHeading(): string
    {
        return 'Mes notifications';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Auth::user()
                    ?->notifications()
                    ->getQuery()
                    ->latest('created_at')
                ?? \App\Models\User::query()->whereRaw('0 = 1')
            )
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => $this->humanReadableType($state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('data')
                    ->label('Données')
                    ->formatStateUsing(fn (?array $state): string => $this->formatNotificationData($state))
                    ->limit(80),

                TextColumn::make('read_at')
                    ->label('Lu le')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i')
                        : 'Non lu')
                    ->color(fn (?string $state): string => $state ? 'success' : 'warning'),

                TextColumn::make('created_at')
                    ->label('Reçue le')
                    ->formatStateUsing(fn ($state): string => \Carbon\Carbon::parse($state)->format('d/m/Y H:i'))
                    ->sortable(),
            ])
            ->actions([
                Action::make('markAsRead')
                    ->label('Marquer comme lu')
                    ->icon('heroicon-m-check')
                    ->visible(fn ($record): bool => $record->read_at === null)
                    ->action(fn ($record) => $this->markAsRead($record->id))
                    ->requiresConfirmation()
                    ->modalHeading('Marquer comme lu')
                    ->modalSubmitActionLabel('Confirmer'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Aucune notification')
            ->emptyStateDescription("Vous n'avez aucune notification.");
    }

    private function humanReadableType(string $type): string
    {
        $map = [
            'App\\Notifications\\NewAppointmentNotification' => 'Nouveau rendez-vous',
            'App\\Notifications\\AppointmentConfirmedNotification' => 'Rendez-vous confirmé',
            'App\\Notifications\\AppointmentCancelledNotification' => 'Rendez-vous annulé',
            'App\\Notifications\\PaymentReceivedNotification' => 'Paiement reçu',
            'App\\Notifications\\InvoiceIssuedNotification' => 'Facture émise',
            'App\\Notifications\\ConsultationCompletedNotification' => 'Consultation terminée',
            'App\\Notifications\\PrescriptionReadyNotification' => 'Ordonnance disponible',
            'App\\Notifications\\LabReportReadyNotification' => 'Rapport de laboratoire disponible',
        ];

        return $map[$type] ?? str_replace('Notification', '', class_basename($type));
    }

    private function formatNotificationData(?array $data): string
    {
        if (! $data) {
            return '—';
        }

        $parts = [];
        foreach ($data as $value) {
            if (is_string($value)) {
                $parts[] = $value;
            }
        }

        return implode(' — ', $parts) ?: '—';
    }

    private function markAsRead(string $notificationId): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();

            Notification::make()
                ->title('Notification marquée comme lue')
                ->success()
                ->send();

            $this->tableBuilder->checkIfRecordIsSelectableUsing(null);
        }
    }
}
