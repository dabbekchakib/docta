<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $fmt = fn (int $value): string => number_format($value, 0, ',', ' ');
    @endphp

    <div class="space-y-6">
        {{-- KPI Principaux --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="relative overflow-hidden rounded-2xl border border-primary-200 bg-white p-5 shadow-sm dark:border-primary-500/30 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-primary-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Total patients</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-primary-600 dark:text-primary-400">{{ $fmt($stats['total']) }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">dossiers enregistrés</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-heroicon-o-user-group class="size-4" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm dark:border-emerald-500/30 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-emerald-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Nouveaux ce mois</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $stats['new_this_month'] }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">+ {{ $stats['new_this_week'] }} cette semaine</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-heroicon-o-user-plus class="size-4" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-blue-200 bg-white p-5 shadow-sm dark:border-blue-500/30 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-blue-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Hommes / Femmes</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-blue-600 dark:text-blue-400">{{ $stats['males'] }} / {{ $stats['females'] }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">répartition par sexe</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <x-heroicon-o-users class="size-4" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-gray-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Actifs / Inactifs</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ $stats['active'] }} / {{ $stats['inactive'] }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $stats['archived'] }} archivé(s)</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                        <x-heroicon-o-user-circle class="size-4" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Couverture sociale --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        <x-heroicon-o-shield-check class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">Couverture CNAM</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $stats['with_cnam'] }} patient(s) couvert(s)</p>
                    </div>
                </div>
                <div class="w-full rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="rounded-full bg-amber-500 py-1 text-center text-xs font-bold text-white dark:bg-amber-400" style="width: {{ $stats['total'] > 0 ? round(($stats['with_cnam'] / $stats['total']) * 100) : 0 }}%">
                        {{ $stats['total'] > 0 ? round(($stats['with_cnam'] / $stats['total']) * 100) : 0 }}%
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <x-heroicon-o-heart class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">Assurance privée</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $stats['with_insurance'] }} patient(s) assuré(s)</p>
                    </div>
                </div>
                <div class="w-full rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="rounded-full bg-blue-500 py-1 text-center text-xs font-bold text-white dark:bg-blue-400" style="width: {{ $stats['total'] > 0 ? round(($stats['with_insurance'] / $stats['total']) * 100) : 0 }}%">
                        {{ $stats['total'] > 0 ? round(($stats['with_insurance'] / $stats['total']) * 100) : 0 }}%
                    </div>
                </div>
            </div>
        </div>

        {{-- Graphiques --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- Évolution des inscriptions --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Évolution des inscriptions</h3>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">12 derniers mois</p>
                </div>
                <div class="p-5">
                    @if (! empty($stats['new_by_month']))
                        <div class="space-y-2">
                            @php
                                $maxMonth = max($stats['new_by_month']);
                            @endphp
                            @foreach ($stats['new_by_month'] as $month => $count)
                                <div class="flex items-center gap-3">
                                    <span class="w-16 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $month }}</span>
                                    <div class="flex-1">
                                        <div class="h-5 rounded-lg bg-emerald-100 dark:bg-emerald-500/10" style="width: {{ $maxMonth > 0 ? ($count / $maxMonth) * 100 : 0 }}%">
                                            <div class="flex h-full items-center justify-end pr-2 rounded-lg bg-emerald-500 dark:bg-emerald-400" style="width: 100%">
                                                <span class="text-[10px] font-bold text-white">{{ $count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-6 text-center text-sm italic text-gray-400">Aucune donnée disponible.</p>
                    @endif
                </div>
            </div>

            {{-- Répartition par gouvernorat --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Répartition par gouvernorat</h3>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Top 10 des régions</p>
                </div>
                <div class="p-5">
                    @if (! empty($stats['by_governorate']))
                        <div class="space-y-2">
                            @php
                                $maxGov = max($stats['by_governorate']);
                            @endphp
                            @foreach ($stats['by_governorate'] as $governorate => $count)
                                <div class="flex items-center gap-3">
                                    <span class="w-24 truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ $governorate }}</span>
                                    <div class="flex-1">
                                        <div class="h-5 rounded-lg bg-primary-100 dark:bg-primary-500/10" style="width: {{ $maxGov > 0 ? ($count / $maxGov) * 100 : 0 }}%">
                                            <div class="flex h-full items-center justify-end pr-2 rounded-lg bg-primary-500 dark:bg-primary-400" style="width: 100%">
                                                <span class="text-[10px] font-bold text-white">{{ $count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-6 text-center text-sm italic text-gray-400">Aucune donnée disponible.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Répartition par âge --}}
        @if (! empty(array_filter($stats['age_groups'])))
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Répartition par tranche d'âge</h3>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Patients avec date de naissance renseignée</p>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-4 gap-3 sm:grid-cols-8">
                        @php
                            $ageColors = ['primary', 'info', 'success', 'warning', 'danger', 'gray', 'primary', 'info'];
                        @endphp
                        @foreach ($stats['age_groups'] as $range => $count)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 text-center dark:border-gray-700 dark:bg-gray-800/60">
                                <p class="text-xl font-extrabold text-{{ $ageColors[$loop->index % count($ageColors)] }}-600 dark:text-{{ $ageColors[$loop->index % count($ageColors)] }}-400">{{ $count }}</p>
                                <p class="mt-1 text-[10px] font-medium text-gray-500 dark:text-gray-400">{{ $range }} ans</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
