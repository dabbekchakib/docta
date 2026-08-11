<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Dossier médical patient</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #111827; line-height: 1.5; }
        .header { display: table; width: 100%; margin-bottom: 20px; border-bottom: 3px solid #0284c7; padding-bottom: 10px; }
        .brand { font-size: 24px; font-weight: bold; color: #0284c7; }
        .brand small { display: block; font-size: 11px; font-weight: normal; color: #4b5563; }
        .cabinet { text-align: right; font-size: 11px; color: #4b5563; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        .subtitle { color: #6b7280; font-size: 11px; margin-bottom: 16px; }
        .box { border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 14px; }
        .box-title { background: #f3f4f6; padding: 6px 10px; font-weight: bold; font-size: 12px; color: #374151; border-bottom: 1px solid #e5e7eb; }
        .box-body { padding: 10px; }
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td { padding: 4px 6px; font-size: 11px; vertical-align: top; }
        table.grid td.label { width: 150px; color: #6b7280; }
        .value { font-weight: 600; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 11px; background: #e0f2fe; color: #075985; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-success { background: #d1fae5; color: #065f46; }
        ul { margin: 0; padding-left: 16px; }
        li { margin-bottom: 3px; font-size: 11px; }
        .empty { color: #9ca3af; font-style: italic; }
        .footer { margin-top: 24px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    @php
        $patient = $record->patient;
        $bloodGroup = $record->full_blood_group ?? $patient?->blood_group?->getLabel();
        $criticalAllergies = $record->allergies->where('status', 'active')->whereIn('severity', ['severe', 'critical']);
        $activeChronic = $record->chronicDiseases->whereIn('status', ['active', 'controlled']);
        $activeMedications = $record->medications->where('status', 'active');
    @endphp

    <div class="header">
        <div class="brand">
            DOCTA
            <small>Logiciel de gestion de cabinet médical</small>
        </div>
        <div class="cabinet">
            <strong>Cabinet Médical</strong><br>
            Avenue de la Liberté, Tunis<br>
            Tél : — 
        </div>
    </div>

    <h1>Dossier médical patient</h1>
    <div class="subtitle">
        N° {{ $record->medical_record_number }} — Ouvert le {{ $record->created_at?->format('d/m/Y') ?? '—' }}
        @if ($bloodGroup)
            <span class="badge">Groupe sanguin : {{ $bloodGroup }}</span>
        @endif
    </div>

    <div class="box">
        <div class="box-title">Identité du patient</div>
        <div class="box-body">
            <table class="grid">
                <tr>
                    <td class="label">Nom complet</td>
                    <td class="value">{{ $patient?->full_name }}</td>
                    <td class="label">N° dossier</td>
                    <td class="value">{{ $patient?->patient_number }}</td>
                </tr>
                <tr>
                    <td class="label">Date de naissance</td>
                    <td class="value">{{ $patient?->birth_date?->format('d/m/Y') }} ({{ $patient?->age }} ans)</td>
                    <td class="label">Sexe</td>
                    <td class="value">{{ $patient?->gender?->getLabel() }}</td>
                </tr>
                <tr>
                    <td class="label">Téléphone</td>
                    <td class="value">{{ $patient?->phone ?? '—' }}</td>
                    <td class="label">Gouvernorat</td>
                    <td class="value">{{ $patient?->governorate?->getLabel() ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Allergies</div>
        <div class="box-body">
            @if ($criticalAllergies->isNotEmpty())
                <p style="margin:0 0 6px 0;">
                    <span class="badge badge-danger">Alerte : allergie(s) critique(s)</span>
                </p>
            @endif
            @if ($record->allergies->isNotEmpty())
                <ul>
                    @foreach ($record->allergies as $allergy)
                        <li>
                            <strong>{{ $allergy->allergen }}</strong>
                            <span class="badge {{ in_array($allergy->severity?->value, ['severe', 'critical'], true) ? 'badge-danger' : 'badge-warning' }}">
                                {{ $allergy->severity?->getLabel() }}
                            </span>
                            @if ($allergy->reaction)
                                — Réaction : {{ $allergy->reaction }}
                            @endif
                            <span class="empty">({{ $allergy->status?->getLabel() }})</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <span class="empty">Aucune allergie enregistrée.</span>
            @endif
        </div>
    </div>

    <div class="box">
        <div class="box-title">Maladies chroniques</div>
        <div class="box-body">
            @if ($record->chronicDiseases->isNotEmpty())
                <ul>
                    @foreach ($record->chronicDiseases as $disease)
                        <li>
                            <strong>{{ $disease->disease_name }}</strong>
                            @if ($disease->icd_code)
                                <span class="badge">{{ $disease->icd_code }}</span>
                            @endif
                            <span class="badge {{ in_array($disease->status?->value, ['active', 'controlled'], true) ? 'badge-warning' : 'badge-success' }}">
                                {{ $disease->status?->getLabel() }}
                            </span>
                            @if ($disease->treatment)
                                — Traitement : {{ $disease->treatment }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <span class="empty">Aucune maladie chronique enregistrée.</span>
            @endif
        </div>
    </div>

    <div class="box">
        <div class="box-title">Antécédents</div>
        <div class="box-body">
            @if ($record->medicalHistories->isNotEmpty())
                <ul>
                    @foreach ($record->medicalHistories as $history)
                        <li>
                            <strong>{{ $history->title }}</strong>
                            @if ($history->type)
                                <span class="badge">{{ $history->type->getLabel() }}</span>
                            @endif
                            @if ($history->diagnosed_at)
                                — {{ $history->diagnosed_at->format('d/m/Y') }}
                            @endif
                            @if ($history->description)
                                — {{ $history->description }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <span class="empty">Aucun antécédent médical enregistré.</span>
            @endif
        </div>
    </div>

    @if ($record->surgicalHistories->isNotEmpty())
        <div class="box">
            <div class="box-title">Antécédents chirurgicaux</div>
            <div class="box-body">
                <ul>
                    @foreach ($record->surgicalHistories as $surgery)
                        <li>
                            <strong>{{ $surgery->procedure_name }}</strong>
                            @if ($surgery->performed_at)
                                — {{ $surgery->performed_at->format('d/m/Y') }}
                            @endif
                            @if ($surgery->hospital)
                                — {{ $surgery->hospital }}
                            @endif
                            @if ($surgery->complications)
                                — Complications : {{ $surgery->complications }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if ($record->familyHistories->isNotEmpty())
        <div class="box">
            <div class="box-title">Antécédents familiaux</div>
            <div class="box-body">
                <ul>
                    @foreach ($record->familyHistories as $history)
                        <li>
                            <strong>{{ $history->condition }}</strong>
                            @if ($history->relative)
                                <span class="badge">{{ $history->relative->getLabel() }}</span>
                            @endif
                            @if ($history->description)
                                — {{ $history->description }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="box">
        <div class="box-title">Traitements en cours</div>
        <div class="box-body">
            @if ($activeMedications->isNotEmpty())
                <ul>
                    @foreach ($activeMedications as $medication)
                        <li>
                            <strong>{{ $medication->name }}</strong>
                            @if ($medication->dosage)
                                — {{ $medication->dosage }}
                            @endif
                            @if ($medication->frequency)
                                — {{ $medication->frequency }}
                            @endif
                            @if ($medication->started_at)
                                — depuis {{ $medication->started_at->format('d/m/Y') }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <span class="empty">Aucun traitement en cours.</span>
            @endif
        </div>
    </div>

    @if ($record->vaccinations->isNotEmpty())
        <div class="box">
            <div class="box-title">Vaccinations</div>
            <div class="box-body">
                <ul>
                    @foreach ($record->vaccinations as $vaccination)
                        <li>
                            <strong>{{ $vaccination->vaccine_name }}</strong>
                            @if ($vaccination->dose_number)
                                <span class="badge badge-success">Dose {{ $vaccination->dose_number }}</span>
                            @endif
                            @if ($vaccination->administered_at)
                                — le {{ $vaccination->administered_at->format('d/m/Y') }}
                            @endif
                            @if ($vaccination->next_due_at)
                                — prochaine dose : {{ $vaccination->next_due_at->format('d/m/Y') }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if ($record->lifestyle)
        <div class="box">
            <div class="box-title">Mode de vie</div>
            <div class="box-body">
                <table class="grid">
                    <tr>
                        <td class="label">Tabac</td>
                        <td class="value">{{ $record->lifestyle->smoking_status?->getLabel() }}</td>
                        <td class="label">Alcool</td>
                        <td class="value">{{ $record->lifestyle->alcohol_status?->getLabel() }}</td>
                    </tr>
                    <tr>
                        <td class="label">Activité physique</td>
                        <td class="value">{{ $record->lifestyle->physical_activity ?? '—' }}</td>
                        <td class="label">Sommeil</td>
                        <td class="value">{{ $record->lifestyle->sleep_quality ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    @endif

    @if ($record->general_notes)
        <div class="box">
            <div class="box-title">Notes générales</div>
            <div class="box-body">
                <div class="html-content">{!! $record->general_notes !!}</div>
            </div>
        </div>
    @endif

    <div class="footer">
        Document généré par DOCTA — {{ now()->format('d/m/Y H:i') }} — Signature du médecin traitant
    </div>
</body>
</html>
