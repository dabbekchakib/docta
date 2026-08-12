<x-filament-panels::page>
    @php
        $ledger = $this->getLedgerData();
        $invalid = $this->invalidRange();
        $cabinet = app(\App\Services\SettingsService::class)->cabinet();
        $fmt = fn (string $value): string => number_format((float) $value, 3, ',', ' ');
        $currency = 'DT';
        $accountType = $ledger['account']?->type;
        $typeColors = [
            'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            'primary' => 'bg-sky-100 text-sky-700 dark:bg-sky-900 dark:text-sky-300',
            'danger' => 'bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-300',
            'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300',
            'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
            'info' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
        ];
        $typeBadge = match ($accountType) {
            \App\Enums\AccountingAccountType::Asset => 'bg-sky-100 text-sky-700 dark:bg-sky-900 dark:text-sky-300',
            \App\Enums\AccountingAccountType::Liability => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
            \App\Enums\AccountingAccountType::Equity => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
            \App\Enums\AccountingAccountType::Revenue => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300',
            default => 'bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-300',
        };
        $typeTile = match ($accountType) {
            \App\Enums\AccountingAccountType::Asset => 'bg-sky-50 text-sky-600 ring-sky-100 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20',
            \App\Enums\AccountingAccountType::Liability => 'bg-amber-50 text-amber-600 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20',
            \App\Enums\AccountingAccountType::Equity => 'bg-blue-50 text-blue-600 ring-blue-100 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/20',
            \App\Enums\AccountingAccountType::Revenue => 'bg-emerald-50 text-emerald-600 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20',
            default => 'bg-rose-50 text-rose-600 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20',
        };
        $periodLabel = trim(($this->from ? \Carbon\Carbon::parse($this->from)->format('d/m/Y') : 'début').' — '.($this->to ? \Carbon\Carbon::parse($this->to)->format('d/m/Y') : 'aujourd\'hui'));
    @endphp

    <style>
        @media print {
            @page { size: A4 landscape; margin: 12mm 10mm 16mm 10mm; }

            html, body {
                background: #fff !important;
                color: #111827 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .fi-sidebar, .fi-topbar, .fi-header, .fi-footer, .fi-page-header, .no-print { display: none !important; }
            .fi-main-ctn, .fi-main { margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
            .fi-page-content, .fi-page-main, .fi-page-content > * { padding: 0 !important; margin: 0 !important; }

            .print-only { display: block !important; }

            .ledger-card {
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                background: #fff !important;
            }
            .ledger-card-header {
                border-bottom: 2px solid #111827 !important;
                padding: 0 0 6px 0 !important;
                margin-bottom: 6px !important;
            }
            .ledger-scroll { overflow: visible !important; max-height: none !important; }

            .ledger-table { font-size: 9px !important; width: 100% !important; border-collapse: collapse !important; }
            .ledger-table thead { display: table-header-group; }
            .ledger-table thead th { position: static !important; }
            .ledger-table tr { break-inside: avoid; page-break-inside: avoid; }
            .ledger-table th {
                background: #f3f4f6 !important;
                color: #111827 !important;
                font-size: 9px !important;
                padding: 5px 6px !important;
                border-bottom: 1px solid #d1d5db !important;
            }
            .ledger-table td {
                padding: 4px 6px !important;
                border-bottom: 1px solid #e5e7eb !important;
                color: #111827 !important;
            }
            .ledger-table tbody tr { background: #fff !important; }
            .ledger-table .print-strong { color: #111827 !important; font-weight: 600 !important; }
            .ledger-table .opening-row td { background: #fefce8 !important; font-weight: 600 !important; }
            .ledger-table .totals-row { background: #f3f4f6 !important; }
            .ledger-table .totals-row td { border-top: 2px solid #111827 !important; font-weight: 700 !important; }

            .ledger-badge {
                background: #fff !important;
                color: #111827 !important;
                border: 1px solid currentColor;
            }
            .ledger-badge::before { display: inline; content: "• "; }

            .ledger-doc-link { color: #111827 !important; text-decoration: none !important; }
            .ledger-doc-link svg { display: none !important; }

            .print-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                display: flex !important;
                justify-content: space-between;
                align-items: center;
                font-size: 8px;
                color: #6b7280;
                border-top: 1px solid #d1d5db;
                padding-top: 3px;
                background: #fff;
            }
            .print-page { white-space: nowrap; }
            .print-page::before { content: "Page "; }
            .print-page::after { content: counter(page); }
        }

        @media screen {
            .print-only { display: none; }
            .ledger-table thead th { position: sticky; top: 0; z-index: 10; }
        }
    </style>

    {{-- En-tête documentaire réservé à l'impression --}}
    <div class="print-only" style="margin-bottom: 12px;">
        <table style="width:100%; border-bottom:3px solid #0284c7; padding-bottom:8px;">
            <tr>
                <td style="vertical-align:top;">
                    <div style="font-size:20px; font-weight:700; color:#0284c7;">{{ $cabinet['cabinet_name'] ?? 'DOCTA' }}</div>
                    <div style="font-size:10px; color:#6b7280;">Logiciel de gestion de cabinet médical</div>
                </td>
                <td style="vertical-align:top; text-align:right; font-size:9px; color:#374151; line-height:1.6;">
                    @if ($cabinet['cabinet_address']) {{ $cabinet['cabinet_address'] }}<br> @endif
                    @if ($cabinet['cabinet_phone']) Tél : {{ $cabinet['cabinet_phone'] }}<br> @endif
                    @if ($cabinet['cabinet_email']) Email : {{ $cabinet['cabinet_email'] }}<br> @endif
                    @if ($cabinet['cabinet_fiscal_number']) Matricule fiscal : {{ $cabinet['cabinet_fiscal_number'] }} @endif
                </td>
            </tr>
        </table>
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:10px;">
            <div>
                <div style="font-size:16px; font-weight:700; color:#111827;">Grand livre</div>
                <div style="font-size:10px; color:#374151; margin-top:2px;">
                    Compte {{ $ledger['account']?->code }} — {{ $ledger['account']?->name }}
                    @if ($accountType) <span style="color:#6b7280;">({{ $accountType->label() }})</span> @endif
                </div>
            </div>
            <div style="text-align:right; font-size:9px; color:#374151; line-height:1.7;">
                Période : {{ $periodLabel }}<br>
                Édité le {{ now()->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

    <div class="space-y-5">
        {{-- Barre de filtres --}}
        <div class="no-print rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-4 flex items-center gap-2">
                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                    <x-heroicon-o-adjustments-horizontal class="size-3.5" />
                </span>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Filtres du grand livre</h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Choisissez un compte et une période pour consulter les mouvements.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Compte comptable</label>
                        <div class="relative">
                            <x-heroicon-o-chevron-up-down class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                            <select
                                wire:model.live="accountId"
                                class="min-w-80 appearance-none rounded-lg border-gray-300 bg-white py-2 pr-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            >
                                <option value="">— Sélectionner un compte —</option>
                                @foreach ($this->groupedAccounts() as $group => $options)
                                    <optgroup label="{{ $group }}">
                                        @foreach ($options as $option)
                                            <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Du</label>
                        <div class="relative">
                            <x-heroicon-o-calendar-days class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                            <input
                                type="date"
                                wire:model.live="from"
                                class="rounded-lg border-gray-300 bg-white py-2 pl-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            >
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Au</label>
                        <div class="relative">
                            <x-heroicon-o-calendar-days class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                            <input
                                type="date"
                                wire:model.live="to"
                                class="rounded-lg border-gray-300 bg-white py-2 pl-9 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            >
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500"
                    >
                        <x-heroicon-o-printer class="size-4" />
                        Imprimer
                    </button>
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-600"
                    >
                        <x-heroicon-o-arrow-path class="size-4" />
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        @if ($invalid)
            <div class="no-print flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300">
                <x-heroicon-o-exclamation-triangle class="size-5 shrink-0" />
                La date de début est postérieure à la date de fin : la période demandée est invalide.
            </div>
        @endif

        @if (! $ledger['account'])
            <div class="no-print rounded-2xl border-2 border-dashed border-gray-200 bg-white/60 p-12 text-center dark:border-gray-700 dark:bg-gray-800/40">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-primary-50 text-primary-500 dark:bg-primary-500/10 dark:text-primary-400">
                    <x-heroicon-o-clipboard-document-list class="size-6" />
                </div>
                <p class="mt-4 text-base font-semibold text-gray-700 dark:text-gray-200">Aucun compte sélectionné</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-gray-500 dark:text-gray-400">
                    Le grand livre retrace l'ensemble des mouvements (débits, crédits et soldes) d'un compte sur une période.
                    Sélectionnez un compte pour afficher ses écritures.
                </p>
            </div>
        @else
            @php
                $openingZero = \App\Support\Money::compare($ledger['opening_balance'] ?? '0', '0') === 0;
                $closingZero = \App\Support\Money::compare($ledger['closing_balance'] ?? '0', '0') === 0;
                $isDebitClose = $ledger['closing_side'] === 'debit';
                $closeText = $isDebitClose ? 'text-primary-600 dark:text-primary-400' : 'text-amber-600 dark:text-amber-400';
                $closeTile = $isDebitClose
                    ? 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400'
                    : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400';
                $closeBorder = $isDebitClose
                    ? 'border-primary-200 dark:border-primary-500/30'
                    : 'border-amber-200 dark:border-amber-500/30';
            @endphp

            {{-- Cartes de synthèse --}}
            <div class="no-print grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Écritures</p>
                            <p class="mt-2 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ $ledger['line_count'] }}</p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">sur la période</p>
                        </div>
                        <div class="flex size-9 items-center justify-center rounded-xl bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                            <x-heroicon-o-document-text class="size-4" />
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-primary-200 bg-white p-5 shadow-sm dark:border-primary-500/30 dark:bg-gray-800">
                    <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-primary-500/10"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Total débits</p>
                            <p class="mt-2 text-3xl font-extrabold tracking-tight text-primary-600 dark:text-primary-400">{{ $fmt($ledger['total_debit']) }} {{ $currency }}</p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">mouvements de période</p>
                        </div>
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            <x-heroicon-o-arrow-trending-down class="size-4" />
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-amber-200 bg-white p-5 shadow-sm dark:border-amber-500/30 dark:bg-gray-800">
                    <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-amber-500/10"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Total crédits</p>
                            <p class="mt-2 text-3xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">{{ $fmt($ledger['total_credit']) }} {{ $currency }}</p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">mouvements de période</p>
                        </div>
                        <div class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                            <x-heroicon-o-arrow-trending-up class="size-4" />
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl border {{ $closeBorder }} bg-white p-5 shadow-sm dark:bg-gray-800">
                    <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-gray-500/10"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Solde de clôture</p>
                            <p class="mt-2 text-3xl font-extrabold tracking-tight {{ $closeText }}">{{ $fmt($ledger['closing_balance']) }} {{ $currency }}</p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $isDebitClose ? 'Solde débiteur' : 'Solde créditeur' }}</p>
                        </div>
                        <div class="flex size-9 items-center justify-center rounded-xl {{ $closeTile }}">
                            <x-heroicon-o-scale class="size-4" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grand livre --}}
            <div class="ledger-card overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="ledger-card-header flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 bg-gray-50/70 px-5 py-4 dark:border-gray-700 dark:bg-gray-800/60">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex size-10 items-center justify-center rounded-xl ring-1 {{ $typeTile }}">
                            <x-heroicon-o-banknotes class="size-4.5" />
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h3 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">{{ $ledger['account']->code }}</h3>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $typeBadge }}">{{ $accountType?->label() }}</span>
                            </div>
                            <p class="mt-0.5 text-sm font-medium text-gray-600 dark:text-gray-300">{{ $ledger['account']->name }}</p>
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Période : {{ $periodLabel }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="min-w-36 rounded-lg bg-white px-3.5 py-2 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Ouverture</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums text-gray-900 dark:text-white">
                                {{ $fmt($ledger['opening_balance']) }} {{ $currency }}
                                @if (! $openingZero)
                                    <span class="ml-1 text-[11px] font-medium {{ $ledger['opening_side'] === 'debit' ? 'text-primary-600 dark:text-primary-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $ledger['opening_side'] === 'debit' ? 'Débiteur' : 'Créditeur' }}
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="min-w-36 rounded-lg bg-white px-3.5 py-2 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clôture</p>
                            <p class="mt-0.5 text-sm font-bold tabular-nums text-gray-900 dark:text-white">
                                {{ $fmt($ledger['closing_balance']) }} {{ $currency }}
                                @if (! $closingZero)
                                    <span class="ml-1 text-[11px] font-medium {{ $isDebitClose ? 'text-primary-600 dark:text-primary-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $isDebitClose ? 'Débiteur' : 'Créditeur' }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="ledger-card-body">
                    <div class="ledger-scroll max-h-[34rem] overflow-auto">
                            <table class="ledger-table min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
                                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs text-gray-500 dark:text-gray-400">Date</th>
                                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs text-gray-500 dark:text-gray-400">N° écriture</th>
                                        <th class="w-[30%] px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs text-gray-500 dark:text-gray-400">Libellé</th>
                                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs text-gray-500 dark:text-gray-400">Nature</th>
                                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs text-gray-500 dark:text-gray-400">Document</th>
                                        <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-xs text-gray-500 dark:text-gray-400">Débit</th>
                                        <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-xs text-gray-500 dark:text-gray-400">Crédit</th>
                                        <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-xs text-gray-500 dark:text-gray-400">Solde</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr class="opening-row bg-amber-50/70 dark:bg-amber-500/5">
                                        <td colspan="5" class="px-4 py-2.5 font-semibold text-gray-900 dark:text-white">
                                            Solde d'ouverture
                                            @if (! $openingZero)
                                                <span class="ml-1 text-xs font-normal text-gray-400 dark:text-gray-500">{{ $ledger['opening_side'] === 'debit' ? 'Débiteur' : 'Créditeur' }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right whitespace-nowrap tabular-nums">
                                            @if ($ledger['opening_side'] === 'debit' && ! $openingZero) {{ $fmt($ledger['opening_balance']) }} @else — @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right whitespace-nowrap tabular-nums">
                                            @if ($ledger['opening_side'] === 'credit' && ! $openingZero) {{ $fmt($ledger['opening_balance']) }} @else — @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right whitespace-nowrap tabular-nums font-semibold print-strong {{ $ledger['opening_side'] === 'debit' ? 'text-primary-600' : 'text-amber-600' }}">
                                            {{ $fmt($ledger['opening_balance']) }} {{ $currency }}
                                        </td>
                                    </tr>
                                    @foreach ($ledger['lines'] as $line)
                                        <tr class="transition-colors odd:bg-white even:bg-gray-50/60 hover:bg-primary-50/50 dark:odd:bg-gray-800/40 dark:even:bg-gray-800/20 dark:hover:bg-gray-700/40">
                                            <td class="px-4 py-2.5 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $line['date'] }}</td>
                                            <td class="px-4 py-2.5 whitespace-nowrap font-semibold print-strong text-primary-600">{{ $line['entry_number'] }}</td>
                                            <td class="px-4 py-2.5">{{ $line['description'] ?? '—' }}</td>
                                            <td class="px-4 py-2.5 whitespace-nowrap">
                                                <span class="ledger-badge inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $typeColors[$line['type_color']] ?? $typeColors['gray'] }}">
                                                    {{ $line['type_label'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 whitespace-nowrap">
                                                @php
                                                    $sourceUrl = $this->sourceUrl($line['source_type'], $line['source_id']);
                                                @endphp
                                                @if ($sourceUrl && $line['source_reference'])
                                                    <a
                                                        href="{{ $sourceUrl }}"
                                                        class="ledger-doc-link inline-flex items-center gap-1 font-medium text-primary-600 hover:text-primary-500 hover:underline"
                                                    >
                                                        {{ $line['source_reference'] }}
                                                        <x-heroicon-o-arrow-top-right-on-square class="size-3.5" />
                                                    </a>
                                                @elseif ($line['source_reference'])
                                                    <span class="text-gray-500 dark:text-gray-400">{{ $line['source_reference'] }}</span>
                                                @else
                                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-right whitespace-nowrap tabular-nums">{{ $line['debit'] ? $fmt($line['debit']) : '—' }}</td>
                                            <td class="px-4 py-2.5 text-right whitespace-nowrap tabular-nums">{{ $line['credit'] ? $fmt($line['credit']) : '—' }}</td>
                                            <td class="px-4 py-2.5 text-right whitespace-nowrap tabular-nums font-semibold print-strong {{ $line['balance_side'] === 'debit' ? 'text-primary-600' : 'text-amber-600' }}">
                                                {{ $fmt($line['balance']) }} {{ $currency }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="totals-row border-t-2 border-gray-300 bg-gray-100 font-semibold dark:border-gray-600 dark:bg-gray-800/60">
                                        <td colspan="5" class="px-4 py-3 text-gray-900 dark:text-white">Totaux de la période</td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap tabular-nums text-gray-900 dark:text-white">{{ $fmt($ledger['total_debit']) }}</td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap tabular-nums text-gray-900 dark:text-white">{{ $fmt($ledger['total_credit']) }}</td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap tabular-nums {{ $closeText }}">
                                            {{ $fmt($ledger['closing_balance']) }} {{ $currency }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                </div>

                <div class="no-print flex items-center justify-between border-t border-gray-200 px-5 py-3 text-xs text-gray-400 dark:border-gray-700 dark:text-gray-500">
                    <span>{{ $ledger['line_count'] }} écriture(s) affichée(s)</span>
                    <span>Document édité le {{ now()->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Pied de page réservé à l'impression --}}
    <div class="print-only print-footer">
        <span>{{ $cabinet['cabinet_name'] ?? 'DOCTA' }} — Grand livre</span>
        <span>Compte {{ $ledger['account']?->code }} — Édité le {{ now()->format('d/m/Y à H:i') }}</span>
        <span class="print-page"></span>
    </div>
</x-filament-panels::page>
