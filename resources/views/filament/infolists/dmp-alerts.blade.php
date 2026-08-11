@php
    use App\DTO\MedicalAlert;
    use App\Enums\MedicalAlertSeverity;

    /** @var \App\Models\MedicalRecord|null $record */
    $record = $medicalRecord ?? null;
    $alerts = $record?->alerts() ?? collect();
@endphp

@if ($alerts->isNotEmpty())
    <div class="grid gap-3">
        @foreach ($alerts as $alert)
            @php
                $styles = match ($alert->severity) {
                    MedicalAlertSeverity::Critical => ['container' => 'border-red-300 bg-red-50 dark:border-red-700/50 dark:bg-red-950/30', 'icon' => 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400', 'title' => 'text-red-800 dark:text-red-300'],
                    MedicalAlertSeverity::Danger => ['container' => 'border-orange-300 bg-orange-50 dark:border-orange-700/50 dark:bg-orange-950/30', 'icon' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/50 dark:text-orange-400', 'title' => 'text-orange-800 dark:text-orange-300'],
                    MedicalAlertSeverity::Warning => ['container' => 'border-amber-300 bg-amber-50 dark:border-amber-700/50 dark:bg-amber-950/30', 'icon' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400', 'title' => 'text-amber-800 dark:text-amber-300'],
                    default => ['container' => 'border-blue-300 bg-blue-50 dark:border-blue-700/50 dark:bg-blue-950/30', 'icon' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400', 'title' => 'text-blue-800 dark:text-blue-300'],
                };
            @endphp
            <div class="fi-section rounded-xl border p-4 {{ $styles['container'] }}">
                <div class="flex items-start gap-3">
                    <div class="fi-icon shrink-0 rounded-lg p-2 {{ $styles['icon'] }}">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="text-sm font-semibold {{ $styles['title'] }}">{{ $alert->title }}</div>
                        <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300">{{ $alert->message }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
