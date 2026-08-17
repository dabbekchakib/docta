<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $fmt = fn (float $value): string => number_format($value, 3, ',', ' ');
    @endphp

    <div class="space-y-6">
        {{-- KPI Rendez-vous --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="relative overflow-hidden rounded-2xl border border-primary-200 bg-white p-5 shadow-sm dark:border-primary-500/30 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-primary-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">RDV ce mois</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-primary-600 dark:text-primary-400">{{ $stats['month_appointments'] }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">rendez-vous programmés</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-heroicon-o-calendar-days class="size-4" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm dark:border-emerald-500/30 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-emerald-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Taux de complétion</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $stats['completion_rate'] }}%</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $stats['month_completed'] }} terminé(s)</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-heroicon-o-check-circle class="size-4" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-rose-200 bg-white p-5 shadow-sm dark:border-rose-500/30 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-rose-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Taux d'absentéisme</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-rose-600 dark:text-rose-400">{{ $stats['no_show_rate'] }}%</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $stats['month_absent'] }} absent(s)</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                        <x-heroicon-o-no-symbol class="size-4" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-amber-200 bg-white p-5 shadow-sm dark:border-amber-500/30 dark:bg-gray-800">
                <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-amber-500/10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Annulés</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">{{ $stats['month_cancelled'] }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">rendez-vous annulés</p>
                    </div>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        <x-heroicon-o-x-circle class="size-4" />
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Financiers du mois --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-heroicon-o-receipt-percent class="size-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Facturé ce mois</p>
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $fmt($stats['month_revenue']) }} DT</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-heroicon-o-banknotes class="size-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Encaissé ce mois</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $fmt($stats['month_collected']) }} DT</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Graphiques --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- RDV journaliers --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Rendez-vous journaliers</h3>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Répartition sur le mois en cours</p>
                </div>
                <div class="p-5">
                    @if (! empty($stats['daily_appointments']))
                        <div class="space-y-2">
                            @php
                                $maxDaily = max($stats['daily_appointments']);
                            @endphp
                            @foreach ($stats['daily_appointments'] as $day => $count)
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

            {{-- Répartition par statut --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Répartition par statut</h3>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Statuts des rendez-vous ce mois</p>
                </div>
                <div class="p-5">
                    @if (! empty($stats['status_distribution']))
                        <div class="space-y-3">
                            @php
                                $statusColors = [
                                    'En attente' => 'warning',
                                    'Confirmé' => 'info',
                                    'En cours' => 'primary',
                                    'Terminé' => 'success',
                                    'Annulé' => 'danger',
                                    'Absent' => 'gray',
                                ];
                                $totalStatus = array_sum($stats['status_distribution']);
                            @endphp
                            @foreach ($stats['status_distribution'] as $status => $count)
                                @php
                                    $color = $statusColors[$status] ?? 'gray';
                                    $pct = $totalStatus > 0 ? round(($count / $totalStatus) * 100) : 0;
                                @endphp
                                <div>
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $status }}</span>
                                        <span class="text-xs font-semibold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $count }} ({{ $pct }}%)</span>
                                    </div>
                                    <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-2.5 rounded-full bg-{{ $color }}-500 dark:bg-{{ $color }}-400" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-6 text-center text-sm italic text-gray-400">Aucune donnée pour ce mois.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top médecins --}}
        @if (! empty($stats['top_doctors']))
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Médecins les plus sollicités</h3>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Top 5 par nombre de rendez-vous ce mois</p>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        @foreach ($stats['top_doctors'] as $doctor => $count)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="flex size-8 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">{{ $loop->iteration }}</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $doctor }}</span>
                                </div>
                                <span class="rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">{{ $count }} RDV</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
