<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Du</label>
                    <input
                        type="date"
                        wire:model.live="from"
                        class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    >
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Au</label>
                    <input
                        type="date"
                        wire:model.live="to"
                        class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    >
                </div>
            </div>
            <button
                type="button"
                wire:click="resetFilters"
                class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-600"
            >
                Réinitialiser
            </button>
        </div>

        @php
            $balance = $this->getBalanceData();
        @endphp

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total débits</p>
                <p class="mt-1 text-2xl font-bold text-primary-600">{{ number_format((float) $balance['total_debit'], 3, ',', ' ') }} DT</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total crédits</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ number_format((float) $balance['total_credit'], 3, ',', ' ') }} DT</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Écritures saisies</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $balance['entries'] }}</p>
            </div>
        </div>

        @if ($balance['total_debit'] !== $balance['total_credit'])
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300">
                La balance n'est pas équilibrée : le total des débits diffère du total des crédits.
            </div>
        @else
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                La balance est équilibrée sur la période sélectionnée.
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-4 py-3 font-semibold text-gray-900 dark:border-gray-700 dark:text-white">
                Balance de vérification
            </div>
            <div class="overflow-x-auto">
                @if ($balance['rows']->isNotEmpty())
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Code</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Compte</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Débit</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Crédit</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Solde</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-500 dark:text-gray-400">Sens</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($balance['rows'] as $row)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap font-semibold text-primary-600">{{ $row['account']->code }}</td>
                                    <td class="px-4 py-2">{{ $row['account']->name }}</td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap">{{ number_format((float) $row['debit'], 3, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap">{{ number_format((float) $row['credit'], 3, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap font-semibold">{{ number_format((float) $row['balance'], 3, ',', ' ') }} DT</td>
                                    <td class="px-4 py-2 text-center">
                                        @if ($row['balance_side'] === 'debit')
                                            <span class="inline-flex rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-900 dark:text-sky-300">Débiteur</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900 dark:text-amber-300">Créditeur</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-50 dark:bg-gray-900">
                                <td colspan="2" class="px-4 py-2 font-semibold text-gray-900 dark:text-white">Totaux</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-white">{{ number_format((float) $balance['total_debit'], 3, ',', ' ') }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-white">{{ number_format((float) $balance['total_credit'], 3, ',', ' ') }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tbody>
                    </table>
                @else
                    <p class="px-4 py-6 text-center text-sm italic text-gray-400">Aucune écriture sur la période.</p>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
