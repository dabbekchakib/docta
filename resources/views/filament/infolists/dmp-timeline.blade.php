@php
    use App\Services\MedicalRecordService;

    /** @var \App\Models\MedicalRecord|null $record */
    $record = $medicalRecord ?? null;
    $events = $record ? app(MedicalRecordService::class)->timeline($record) : collect();

    $dotStyles = [
        'info' => 'bg-blue-500',
        'gray' => 'bg-gray-400',
        'purple' => 'bg-purple-500',
        'danger' => 'bg-red-500',
        'warning' => 'bg-amber-500',
        'success' => 'bg-emerald-500',
        'primary' => 'bg-indigo-500',
    ];
@endphp

@if ($events->isEmpty())
    <div class="fi-section rounded-xl border border-gray-200 p-6 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
        Aucun événement médical enregistré.
    </div>
@else
    <ol class="relative border-s border-gray-200 dark:border-white/10">
        @foreach ($events as $event)
            <li class="mb-6 ms-6">
                <span class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full ring-4 ring-white dark:ring-gray-900 {{ $dotStyles[$event['color']] ?? 'bg-gray-400' }}"></span>
                <div class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ $event['date']?->format('d/m/Y') }}
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-white/5 dark:text-gray-300">
                        {{ $event['typeLabel'] }}
                    </span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $event['title'] }}</span>
                </div>
                @if ($event['description'])
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $event['description'] }}</p>
                @endif
            </li>
        @endforeach
    </ol>
@endif
