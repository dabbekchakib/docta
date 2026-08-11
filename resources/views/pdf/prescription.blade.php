<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Ordonnance médicale</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #111827; line-height: 1.5; }
        .header { display: table; width: 100%; margin-bottom: 18px; border-bottom: 3px solid #0284c7; padding-bottom: 10px; }
        .brand { font-size: 24px; font-weight: bold; color: #0284c7; }
        .brand small { display: block; font-size: 11px; font-weight: normal; color: #4b5563; }
        .qr { text-align: right; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        .subtitle { color: #6b7280; font-size: 11px; margin-bottom: 16px; }
        .box { border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 14px; }
        .box-title { background: #f3f4f6; padding: 6px 10px; font-weight: bold; font-size: 12px; color: #374151; border-bottom: 1px solid #e5e7eb; }
        .box-body { padding: 10px; }
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td { padding: 4px 6px; font-size: 11px; vertical-align: top; }
        table.grid td.label { width: 160px; color: #6b7280; }
        .value { font-weight: 600; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 11px; background: #e0f2fe; color: #075985; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .prescription-title { text-align: center; font-size: 20px; font-weight: bold; letter-spacing: 4px; color: #0284c7; margin: 20px 0; }
        table.medicines { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.medicines th { background: #f3f4f6; text-align: left; font-size: 11px; color: #374151; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        table.medicines td { padding: 8px; font-size: 11px; vertical-align: top; border-bottom: 1px solid #f3f4f6; }
        .medicine-name { font-weight: bold; font-size: 12px; }
        .posology { color: #4b5563; }
        .notes { color: #6b7280; }
        .footer { margin-top: 28px; display: table; width: 100%; }
        .footer .signature { width: 50%; text-align: center; }
        .footer .signature .line { border-top: 1px solid #9ca3af; margin-top: 60px; padding-top: 4px; font-size: 11px; color: #4b5563; }
        .empty { color: #9ca3af; font-style: italic; }
    </style>
</head>
<body>
    @php
        $patient = $prescription->patient;
        $doctor = $prescription->doctor;
        $status = $prescription->status;
    @endphp

    <div class="header">
        <div class="brand" style="display:inline-block;">
            DOCTA
            <small>Logiciel de gestion de cabinet médical</small>
        </div>
        <div style="display:inline-block; text-align:right; float:right;">
            <strong>Cabinet Médical</strong><br>
            Avenue de la Liberté, Tunis<br>
            Tél : {{ $doctor?->phone ?? '—' }}
        </div>
    </div>

    @if ($qrDataUri)
        <div class="qr">
            <img src="{{ $qrDataUri }}" alt="QR Code" width="90">
        </div>
    @endif

    <h1>Ordonnance</h1>
    <div class="subtitle">
        N° {{ $prescription->prescription_number }}
        — Date : {{ $prescription->prescription_date?->format('d/m/Y') ?? '—' }}
        <span class="badge {{ $status->value === 'issued' ? 'badge-success' : 'badge-danger' }}">{{ $status->label() }}</span>
        @if ($prescription->valid_until)
            — Valable jusqu'au {{ $prescription->valid_until->format('d/m/Y') }}
        @endif
    </div>

    <div class="box">
        <div class="box-title">Médecin prescripteur</div>
        <div class="box-body">
            <table class="grid">
                <tr>
                    <td class="label">Nom</td>
                    <td class="value">{{ $doctor?->full_name ?? '—' }}</td>
                    <td class="label">Spécialité</td>
                    <td class="value">{{ $doctor?->speciality?->getLabel() ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">N° d'ordre</td>
                    <td class="value">{{ $doctor?->order_number ?? '—' }}</td>
                    <td class="label">Téléphone</td>
                    <td class="value">{{ $doctor?->phone ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Adresse</td>
                    <td class="value" colspan="3">{{ trim(implode(', ', array_filter([$doctor?->address, $doctor?->city, $doctor?->governorate?->getLabel()]))) ?: '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Patient</div>
        <div class="box-body">
            <table class="grid">
                <tr>
                    <td class="label">Nom</td>
                    <td class="value">{{ $patient?->full_name ?? '—' }}</td>
                    <td class="label">N° patient</td>
                    <td class="value">{{ $patient?->patient_number ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Date de naissance</td>
                    <td class="value">{{ $patient?->birth_date?->format('d/m/Y') ?? '—' }} @if ($patient?->age) ({{ $patient->age }} ans) @endif</td>
                    <td class="label">Sexe</td>
                    <td class="value">{{ $patient?->gender?->getLabel() ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="prescription-title">ORDONNANCE</div>

    <div class="box">
        <div class="box-title">Médicaments prescrits</div>
        <div class="box-body">
            @if ($prescription->items->isNotEmpty())
                <table class="medicines">
                    <thead>
                        <tr>
                            <th style="width:30px;">#</th>
                            <th>Médicament</th>
                            <th>Posologie</th>
                            <th>Durée</th>
                            <th>Quantité</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prescription->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="medicine-name">{{ $item->medicine_name }}</div>
                                    @if ($item->dosage || $item->form)
                                        <div class="notes">{{ collect([$item->dosage, $item->form?->label() ?? null])->filter()->implode(' — ') }}</div>
                                    @endif
                                    @if ($item->active_ingredient)
                                        <div class="notes">Principe actif : {{ $item->active_ingredient }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="posology">
                                        @if ($item->route) {{ $item->route->label() }} @endif
                                        @if ($item->frequency) — {{ $item->frequency }} @endif
                                    </div>
                                    @if ($item->instructions)
                                        <div class="notes">{{ $item->instructions }}</div>
                                    @endif
                                </td>
                                <td class="posology">
                                    @if ($item->duration && $item->duration_unit)
                                        {{ $item->duration }} {{ $item->duration_unit->label().($item->duration > 1 ? 's' : '') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="posology">{{ $item->quantity ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <span class="empty">Aucun médicament enregistré sur cette ordonnance.</span>
            @endif
        </div>
    </div>

    @if ($prescription->notes)
        <div class="box">
            <div class="box-title">Instructions complémentaires</div>
            <div class="box-body">{{ $prescription->notes }}</div>
        </div>
    @endif

    <div class="footer">
        <div class="signature">
            <div class="line">Date : {{ now()->format('d/m/Y') }} — Signature du médecin</div>
        </div>
    </div>
</body>
</html>
