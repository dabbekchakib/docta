<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Compte rendu d'analyses</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #111827; line-height: 1.5; }
        .header { display: table; width: 100%; margin-bottom: 18px; border-bottom: 3px solid #0284c7; padding-bottom: 10px; }
        .brand { font-size: 24px; font-weight: bold; color: #0284c7; }
        .brand small { display: block; font-size: 11px; font-weight: normal; color: #4b5563; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        .subtitle { color: #6b7280; font-size: 11px; margin-bottom: 16px; }
        .box { border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 14px; }
        .box-title { background: #f3f4f6; padding: 6px 10px; font-weight: bold; font-size: 12px; color: #374151; border-bottom: 1px solid #e5e7eb; }
        .box-body { padding: 10px; }
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td { padding: 4px 6px; font-size: 11px; vertical-align: top; }
        table.grid td.label { width: 170px; color: #6b7280; }
        .value { font-weight: 600; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 11px; background: #e0f2fe; color: #075985; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        table.results { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.results th { background: #f3f4f6; text-align: left; font-size: 11px; color: #374151; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        table.results td { padding: 8px; font-size: 11px; vertical-align: top; border-bottom: 1px solid #f3f4f6; }
        .result-value { font-weight: bold; font-size: 12px; }
        .abnormal { color: #b45309; font-weight: 600; }
        .critical { color: #991b1b; font-weight: 700; }
        .empty { color: #9ca3af; font-style: italic; }
        .footer { margin-top: 28px; display: table; width: 100%; }
        .footer .signature { width: 50%; text-align: center; }
        .footer .signature .line { border-top: 1px solid #9ca3af; margin-top: 60px; padding-top: 4px; font-size: 11px; color: #4b5563; }
        .summary { background: #eff6ff; border-left: 4px solid #0284c7; padding: 8px 10px; font-size: 11px; color: #075985; }
    </style>
</head>
<body>
    @php
        $request = $report->request;
        $patient = $request?->patient;
        $doctor = $request?->doctor;
        $laboratory = $request?->laboratory;
    @endphp

    <div class="header">
        <div class="brand" style="display:inline-block;">
            DOCTA
            <small>Logiciel de gestion de cabinet médical</small>
        </div>
        <div style="display:inline-block; text-align:right; float:right;">
            <strong>Laboratoire d'analyses médicales</strong><br>
            {{ $laboratory?->name ?? '—' }}<br>
            {{ trim(implode(', ', array_filter([$laboratory?->address, $laboratory?->city]))) ?: '' }}<br>
            Tél : {{ $laboratory?->phone ?? '—' }}
        </div>
    </div>

    <h1>Compte rendu d'analyses</h1>
    <div class="subtitle">
        N° {{ $report->report_number }}
        — Date : {{ $report->report_date?->format('d/m/Y') ?? '—' }}
        @if ($report->validated_at)
            — Validé le {{ $report->validated_at->format('d/m/Y H:i') }}
        @endif
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

    <div class="box">
        <div class="box-title">Demande et prescripteur</div>
        <div class="box-body">
            <table class="grid">
                <tr>
                    <td class="label">N° demande</td>
                    <td class="value">{{ $request?->request_number ?? '—' }}</td>
                    <td class="label">Date de la demande</td>
                    <td class="value">{{ $request?->requested_at?->format('d/m/Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Médecin prescripteur</td>
                    <td class="value">{{ $doctor?->full_name ?? '—' }}</td>
                    <td class="label">Spécialité</td>
                    <td class="value">{{ $doctor?->speciality?->getLabel() ?? '—' }}</td>
                </tr>
                @if ($request?->clinical_information)
                    <tr>
                        <td class="label">Informations cliniques</td>
                        <td class="value" colspan="3">{{ $request->clinical_information }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Résultats des examens</div>
        <div class="box-body">
            @if ($request?->items->isNotEmpty())
                <table class="results">
                    <thead>
                        <tr>
                            <th style="width:28px;">#</th>
                            <th>Examen</th>
                            <th>Paramètre</th>
                            <th>Valeur</th>
                            <th>Unité</th>
                            <th>Référence</th>
                            <th>Anomalie</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($request->items as $item)
                            @forelse ($item->results as $result)
                                <tr>
                                    <td>{{ $loop->parent->iteration }}</td>
                                    <td class="value">{{ $item->test?->name ?? '—' }}</td>
                                    <td>{{ $result->parameter_name ?? '—' }}</td>
                                    <td class="result-value {{ $result->isCritical() ? 'critical' : ($result->isOutOfRange() ? 'abnormal' : '') }}">{{ $result->value ?? '—' }}</td>
                                    <td>{{ $result->unit ?? '—' }}</td>
                                    <td>
                                        @if ($result->reference_text)
                                            {{ $result->reference_text }}
                                        @else
                                            @if ($result->reference_min !== null && $result->reference_max !== null)
                                                {{ $result->reference_min }} – {{ $result->reference_max }}
                                            @elseif ($result->reference_min !== null)
                                                ≥ {{ $result->reference_min }}
                                            @elseif ($result->reference_max !== null)
                                                ≤ {{ $result->reference_max }}
                                            @else
                                                —
                                            @endif
                                            @if ($result->unit) {{ $result->unit }} @endif
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $abnormality = $result->abnormality;
                                        @endphp
                                        <span class="badge {{ $abnormality?->getColor() === 'danger' ? 'badge-danger' : ($abnormality?->getColor() === 'warning' ? 'badge-warning' : 'badge-success') }}">
                                            {{ $abnormality?->getLabel() ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="value" colspan="6">{{ $item->test?->name ?? '—' }} — <span class="empty">Aucun résultat enregistré</span></td>
                                </tr>
                            @endforelse
                        @endforeach
                    </tbody>
                </table>
            @else
                <span class="empty">Aucun examen enregistré sur cette demande.</span>
            @endif
        </div>
    </div>

    @if ($report->summary)
        <div class="box">
            <div class="box-title">Synthèse</div>
            <div class="box-body summary">{{ $report->summary }}</div>
        </div>
    @endif

    @if ($report->comments)
        <div class="box">
            <div class="box-title">Commentaires</div>
            <div class="box-body">{{ $report->comments }}</div>
        </div>
    @endif

    <div class="footer">
        <div class="signature">
            <div class="line">Validé par le laboratoire — {{ $report->validatedBy?->name ?? 'Biologiste' }}</div>
        </div>
    </div>
</body>
</html>
