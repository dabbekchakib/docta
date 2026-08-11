@php
    use App\Enums\AlcoholStatus;
    use App\Enums\SmokingStatus;

    /** @var \App\Models\MedicalRecord|null $record */
    $record = $medicalRecord ?? null;
    $lifestyle = $record?->lifestyle;
@endphp

@if (! $lifestyle)
    <div class="fi-section rounded-xl border border-gray-200 p-6 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
        Aucune information sur le mode de vie enregistrée.
    </div>
@else
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Tabac</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                {{ $lifestyle->smoking_status?->getLabel() ?? '—' }}
                @if ($lifestyle->smoking_status === SmokingStatus::Current && $lifestyle->smoking_quantity)
                    <span class="font-normal text-gray-500 dark:text-gray-400">({{ $lifestyle->smoking_quantity }})</span>
                @endif
            </dd>
        </div>
        <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Alcool</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $lifestyle->alcohol_status?->getLabel() ?? '—' }}</dd>
        </div>
        <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Activité physique</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $lifestyle->physical_activity ?: '—' }}</dd>
        </div>
        <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Alimentation</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $lifestyle->diet ?: '—' }}</dd>
        </div>
        <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Qualité du sommeil</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $lifestyle->sleep_quality ?: '—' }}</dd>
        </div>
        <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Risque professionnel</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $lifestyle->occupation_risk ?: '—' }}</dd>
        </div>
    </dl>
    @if ($lifestyle->other_risks || $lifestyle->notes)
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            @if ($lifestyle->other_risks)
                <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Autres risques</div>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $lifestyle->other_risks }}</p>
                </div>
            @endif
            @if ($lifestyle->notes)
                <div class="fi-section rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Notes</div>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $lifestyle->notes }}</p>
                </div>
            @endif
        </div>
    @endif
@endif
