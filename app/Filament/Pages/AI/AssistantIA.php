<?php

namespace App\Filament\Pages\AI;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Enums\Width;

class AssistantIA extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'Assistant IA';

    protected static ?string $navigationLabel = 'Assistant IA';

    protected static ?string $title = '';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.ai.assistant-ia';

    protected static bool $shouldCheckNavigationItems = false;

    public ?string $contextType = null;

    public ?int $contextId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission(['assistant.ia.use', 'assistant.ia.view']) ?? false;
    }

    public function mount(): void
    {
        $this->contextType = request()->query('context_type');
        $this->contextId = request()->query('context_id') ? (int) request()->query('context_id') : null;
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getPageClasses(): array
    {
        return ['fi-height-full', 'assistant-ia-full'];
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
