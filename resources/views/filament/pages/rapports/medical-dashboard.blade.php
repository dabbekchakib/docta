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
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Aujourd'hui</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-primary-600 dark:text-primary-400">{{ $stats['today'] }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">consultation(s)</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-heroicon-o-clipboard-document-list class="size-4" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-blue-200 bg-white p-5 shadow-sm dark:border-blue-500/30 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-blue-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Cette semaine</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-blue-600 dark:text-blue-400">{{ $stats['week'] }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">du {{ now()->startOfWeek()->format('d/m') }} au {{ now()->endOfWeek()->format('d/m') }}</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <x-heroicon-o-calendar-days class="size-4" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm dark:border-emerald-500/30 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-emerald-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Ce mois</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $stats['month'] }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">consultation(s)</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-heroicon-o-arrow-trending-up class="size-4" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-gray-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Total</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ $fmt($stats['total']) }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">consultation(s) enregistrée(s)</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                        <x-heroicon-o-document-text class="size-4" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Indicateurs du jour --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-heroicon-o-check-circle class="size-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Terminées aujourd'hui</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['completed_today'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                        <x-heroicon-o-x-circle class="size-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Annulées aujourd'hui</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['cancelled_today'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        <x-heroicon-o-document-text class="size-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Ordonnances ce mois</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['month_prescriptions'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Graphiques --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- Consultations par jour --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Consultations journalières</h3>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Répartition sur le mois en cours</p>
                </div>
                <div class="p-5">
                    @if (! empty($stats['daily_consultations']))
                        <div class="space-y-2">
                            @php
                                $maxDaily = max($stats['daily_consultations']);
                            @endphp
                            @foreach ($stats['daily_consultations'] as $day => $count)
                                <div class="flex items-center gap-3">
                                    <span class="w-12 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $day }}</span>
                                    <div class="flex-1">
                                        <div class="h-5 rounded-lg bg-primary-100 dark:bg-primary-500/10" style="width: {{ $maxDaily > 0 ? ($count / $maxDaily) * 100 : 0 }}%">
                                            <div class="flex h-full items-center justify-end pr-2 rounded-lg bg-primary-500 dark:bg-primary-400" style="width: 100%">
                                                <span class="text-[10px] font-bold text-white">{{ $count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-6 text-center text-sm italic text-gray-400">Aucune donnée pour ce mois.</p>
                    @endif
                </div>
            </div>

            {{-- Top médecins --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Médecins les plus actifs</h3>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Top 5 ce mois (consultations terminées)</p>
                </div>
                <div class="p-5">
                    @if (! empty($stats['top_doctors']))
                        <div class="space-y-3">
                            @foreach ($stats['top_doctors'] as $doctor => $count)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-8 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">{{ $loop->iteration }}</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $doctor }}</span>
                                    </div>
                                    <span class="rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-6 text-center text-sm italic text-gray-400">Aucune donnée pour ce mois.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Répartition par type --}}
        @if (! empty($stats['consultation_types']))
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Répartition par type de consultation</h3>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Mois en cours</p>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @php
                            $typeColors = ['primary', 'info', 'success', 'warning', 'danger'];
                        @endphp
                        @foreach ($stats['consultation_types'] as $type => $count)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 text-center dark:border-gray-700 dark:bg-gray-800/60">
                                <p class="text-2xl font-extrabold text-{{ $typeColors[$loop->index % count($typeColors)] }}-600 dark:text-{{ $typeColors[$loop->index % count($typeColors)] }}-400">{{ $count }}</p>
                                <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $type }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
