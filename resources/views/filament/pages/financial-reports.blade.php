<x-filament-panels::page>
    <div class="space-y-4">
        @php
            $report = $this->getReportData();
        @endphp

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Facturé (total)</p>
                <p class="mt-1 text-2xl font-bold text-primary-600">{{ number_format((float) $report['overview']['billed'], 3, ',', ' ') }} DT</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Encaissé (total)</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format((float) $report['overview']['collected'], 3, ',', ' ') }} DT</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Restant dû</p>
                <p class="mt-1 text-2xl font-bold text-rose-600">{{ number_format((float) $report['overview']['outstanding'], 3, ',', ' ') }} DT</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Mois en cours</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ number_format((float) $report['monthly']['billed'], 3, ',', ' ') }} DT</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Encaissé : {{ number_format((float) $report['monthly']['collected'], 3, ',', ' ') }} DT</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-4 py-3 font-semibold text-gray-900 dark:border-gray-700 dark:text-white">
                    Encaissements du jour ({{ $report['daily']['date']?->format('d/m/Y') ?? '—' }})
                    <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                        Total : {{ number_format((float) $report['daily']['total'], 3, ',', ' ') }} DT — {{ $report['daily']['count'] }} paiement(s)
                    </span>
                </div>
                <div class="overflow-x-auto">
                    @if ($report['daily']['payments']->isNotEmpty())
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">N° paiement</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Patient</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Facture</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Moyen</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($report['daily']['payments'] as $payment)
                                    <tr>
                                        <td class="px-4 py-2">{{ $payment->payment_number }}</td>
                                        <td class="px-4 py-2">{{ $payment->invoice?->patient?->full_name ?? '—' }}</td>
                                        <td class="px-4 py-2">{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                                        <td class="px-4 py-2">{{ $payment->paymentMethod?->name ?? '—' }}</td>
                                        <td class="px-4 py-2 text-right font-semibold">{{ number_format((float) $payment->amount, 3, ',', ' ') }} DT</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="px-4 py-6 text-center text-sm italic text-gray-400">Aucun encaissement aujourd'hui.</p>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-4 py-3 font-semibold text-gray-900 dark:border-gray-700 dark:text-white">
                    Factures en retard ({{ $report['overdue']->count() }})
                </div>
                <div class="overflow-x-auto">
                    @if ($report['overdue']->isNotEmpty())
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">N° facture</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Patient</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Échéance</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Restant dû</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($report['overdue'] as $invoice)
                                    <tr>
                                        <td class="px-4 py-2">{{ $invoice->invoice_number }}</td>
                                        <td class="px-4 py-2">{{ $invoice->patient?->full_name ?? '—' }}</td>
                                        <td class="px-4 py-2">{{ $invoice->due_date?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-rose-600">{{ number_format((float) $invoice->amount_remaining, 3, ',', ' ') }} DT</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="px-4 py-6 text-center text-sm italic text-gray-400">Aucune facture en retard.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
