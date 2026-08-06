<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->getMonthLabel() }}</h2>

            <div class="flex flex-wrap items-center gap-2">
                @unless ($this->isDoctorUser())
                    <select
                        wire:model.live="doctorId"
                        class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="">Tous les médecins</option>
                        @foreach ($this->doctorsForFilter() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                @endunless

                <button
                    type="button"
                    wire:click="previousMonth"
                    class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-600"
                >
                    &#8592;
                </button>
                <button
                    type="button"
                    wire:click="goToToday"
                    class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-600"
                >
                    Aujourd'hui
                </button>
                <button
                    type="button"
                    wire:click="nextMonth"
                    class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-600"
                >
                    &#8594;
                </button>

                <a
                    href="{{ $this->getCreateAppointmentUrl() }}"
                    class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500"
                >
                    + Nouveau RDV
                </a>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-px overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700">
            @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $label)
                <div class="bg-gray-100 px-2 py-2 text-center text-xs font-semibold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ $label }}
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-px overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700">
            @foreach ($this->getCalendarDates() as $date)
                @php
                    $day = \Illuminate\Support\Carbon::parse($date);
                    $inMonth = $this->isInCurrentMonth($date);
                    $isToday = $date === now()->format('Y-m-d');
                    $appts = $this->appointmentsFor($date);
                @endphp
                <div class="min-h-28 bg-white p-2 dark:bg-gray-900 {{ $inMonth ? '' : 'opacity-40' }} {{ $isToday ? 'ring-1 ring-inset ring-primary-500' : '' }}">
                    <div class="mb-1 flex items-center justify-between">
                        <span class="text-sm font-semibold {{ $isToday ? 'text-primary-600' : 'text-gray-700 dark:text-gray-300' }}">
                            {{ $day->format('j') }}
                        </span>
                    </div>
                    <div class="space-y-1">
                        @forelse ($appts as $appt)
                            <a
                                href="{{ $this->getAppointmentUrl($appt->id) }}"
                                class="block truncate rounded px-1.5 py-1 text-[11px] leading-tight text-white hover:opacity-80"
                                style="background-color: {{ $this->appointmentStatusColor($appt) }}"
                                title="{{ $appt->start_time }} - {{ $appt->patient?->full_name }} ({{ $appt->status->label() }})"
                            >
                                {{ $appt->start_time }} &#183; {{ $appt->patient?->full_name }}
                            </a>
                        @empty
                            <span class="block text-[10px] text-gray-300 dark:text-gray-700">&nbsp;</span>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
