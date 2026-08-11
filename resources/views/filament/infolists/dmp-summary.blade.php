@php
    use App\Services\MedicalRecordService;

    /** @var \App\Models\MedicalRecord|null $record */
    $record = $medicalRecord ?? null;
    $summary = $record ? app(MedicalRecordService::class)->summary($record) : null;
    $hasRecord = $record !== null;
@endphp

@if (! $hasRecord)
    <div class="fi-section rounded-xl border border-gray-200 p-6 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
        Aucun dossier médical ouvert pour ce patient.
    </div>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Groupe sanguin</div>
            <div class="mt-1 flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-beaker" class="h-4 w-4 text-red-500" />
                <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $summary['blood_group'] ?? '—' }}</span>
            </div>
        </div>
        <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Allergies critiques</div>
            <div class="mt-1 flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-4 w-4 {{ $summary['critical_allergies']->isNotEmpty() ? 'text-red-500' : 'text-gray-400' }}" />
                <span class="text-lg font-bold {{ $summary['critical_allergies']->isNotEmpty() ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
                    {{ $summary['critical_allergies']->count() }}
                </span>
            </div>
        </div>
        <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Maladies actives</div>
            <div class="mt-1 flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-heart" class="h-4 w-4 {{ $summary['chronic_diseases']->isNotEmpty() ? 'text-amber-500' : 'text-gray-400' }}" />
                <span class="text-lg font-bold {{ $summary['chronic_diseases']->isNotEmpty() ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-gray-100' }}">
                    {{ $summary['chronic_diseases']->count() }}
                </span>
            </div>
        </div>
        <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Traitements actifs</div>
            <div class="mt-1 flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4 {{ $summary['medications']->isNotEmpty() ? 'text-blue-500' : 'text-gray-400' }}" />
                <span class="text-lg font-bold {{ $summary['medications']->isNotEmpty() ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-gray-100' }}">
                    {{ $summary['medications']->count() }}
                </span>
            </div>
        </div>
    </div>

    @if ($summary['critical_allergies']->isNotEmpty() || $summary['chronic_diseases']->isNotEmpty() || $summary['medications']->isNotEmpty())
        <div class="mt-4 grid gap-4 lg:grid-cols-3">
            @if ($summary['critical_allergies']->isNotEmpty())
                <div class="fi-section rounded-xl border border-red-200 bg-red-50/50 p-4 dark:border-red-700/50 dark:bg-red-950/20">
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-400">Allergies critiques</div>
                    <ul class="space-y-1 text-sm text-red-800 dark:text-red-300">
                        @foreach ($summary['critical_allergies'] as $allergy)
                            <li class="flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-3.5 w-3.5 shrink-0" />
                                <span>{{ $allergy->allergen }} <span class="text-xs">({{ $allergy->severity->getLabel() }})</span></span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if ($summary['chronic_diseases']->isNotEmpty())
                <div class="fi-section rounded-xl border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-700/50 dark:bg-amber-950/20">
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-400">Maladies chroniques actives</div>
                    <ul class="space-y-1 text-sm text-amber-800 dark:text-amber-300">
                        @foreach ($summary['chronic_diseases'] as $disease)
                            <li>{{ $disease->disease_name }}{{ $disease->icd_code ? " ({$disease->icd_code})" : '' }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if ($summary['medications']->isNotEmpty())
                <div class="fi-section rounded-xl border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-700/50 dark:bg-blue-950/20">
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-400">Traitements en cours</div>
                    <ul class="space-y-1 text-sm text-blue-800 dark:text-blue-300">
                        @foreach ($summary['medications'] as $medication)
                            <li>{{ $medication->name }} <span class="text-xs">({{ $medication->dosage }}{{ $medication->frequency ? ' — '.$medication->frequency : '' }})</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
@endif
