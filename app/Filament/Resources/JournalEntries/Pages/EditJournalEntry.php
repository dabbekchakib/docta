<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Models\JournalEntryLine;
use App\Services\JournalEntryService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['lines'] = $this->record->lines()
            ->orderBy('id')
            ->get()
            ->map(fn (JournalEntryLine $line): array => [
                'accounting_account_id' => $line->accounting_account_id,
                'debit' => (float) $line->debit,
                'credit' => (float) $line->credit,
                'notes' => $line->notes,
            ])
            ->all();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(JournalEntryService::class)->updateDraft($record, [
                'entry_date' => $data['entry_date'],
                'description' => $data['description'] ?? null,
            ], $data['lines'] ?? []);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['lines' => $e->getMessage()]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
