<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport de consultation</title>
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
        .html-content { font-size: 12px; }
        .html-content p, .html-content ul, .html-content ol { margin: 0 0 6px 0; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 11px; background: #e0f2fe; color: #075985; }
        .footer { margin-top: 24px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">
            DOCTA
            <small>Logiciel de gestion de cabinet médical</small>
        </div>
        <div class="cabinet">
            <strong>Cabinet Médical</strong><br>
            {{ $consultation->doctor?->address ?? 'Avenue de la Liberté' }}, {{ $consultation->doctor?->city ?? 'Tunis' }}<br>
            Tél : {{ $consultation->doctor?->phone ?? '—' }}
        </div>
    </div>

    <h1>Rapport de consultation</h1>
    <div class="subtitle">
        N° {{ $consultation->consultation_number }} — {{ $consultation->consultation_date?->format('d/m/Y') ?? '—' }}
        <span class="badge">{{ $consultation->status?->label() }}</span>
    </div>

    <div class="box">
        <div class="box-title">Patient</div>
        <div class="box-body">
            <table class="grid">
                <tr>
                    <td class="label">Nom complet</td>
                    <td class="value">{{ $consultation->patient?->full_name }}</td>
                    <td class="label">N° dossier</td>
                    <td class="value">{{ $consultation->patient?->patient_number }}</td>
                </tr>
                <tr>
                    <td class="label">Date de naissance</td>
                    <td class="value">{{ $consultation->patient?->birth_date?->format('d/m/Y') }} ({{ $consultation->patient?->age }} ans)</td>
                    <td class="label">Sexe</td>
                    <td class="value">{{ $consultation->patient?->gender?->label() }}</td>
                </tr>
                <tr>
                    <td class="label">Groupe sanguin</td>
                    <td class="value">{{ $consultation->patient?->blood_group?->getLabel() ?? '—' }}</td>
                    <td class="label">Téléphone</td>
                    <td class="value">{{ $consultation->patient?->phone ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Allergies</td>
                    <td colspan="3" class="value">{{ $consultation->patient?->allergies ?? 'Aucune allergie connue' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Médecin &amp; motifs de consultation</div>
        <div class="box-body">
            <table class="grid">
                <tr>
                    <td class="label">Médecin</td>
                    <td class="value">Dr {{ $consultation->doctor?->full_name }} — {{ $consultation->doctor?->speciality?->getLabel() }}</td>
                    <td class="label">Type</td>
                    <td class="value">{{ $consultation->type?->label() }}</td>
                </tr>
                <tr>
                    <td class="label">Heures</td>
                    <td class="value">{{ $consultation->start_time ?? '—' }}{{ $consultation->end_time ? ' à '.$consultation->end_time : '' }}</td>
                    <td class="label">Rendez-vous lié</td>
                    <td class="value">{{ $consultation->appointment?->appointment_number ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Motif</td>
                    <td colspan="3" class="value">{{ $consultation->reason ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Symptômes</td>
                    <td colspan="3" class="value">{{ $consultation->symptoms ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Constantes vitales</div>
        <div class="box-body">
            <table class="grid">
                <tr>
                    <td class="label">Température</td>
                    <td class="value">{{ $consultation->vitalSign?->temperature !== null ? $consultation->vitalSign->temperature.' °C' : '—' }}</td>
                    <td class="label">Poids</td>
                    <td class="value">{{ $consultation->vitalSign?->weight !== null ? $consultation->vitalSign->weight.' kg' : '—' }}</td>
                    <td class="label">Taille</td>
                    <td class="value">{{ $consultation->vitalSign?->height !== null ? $consultation->vitalSign->height.' cm' : '—' }}</td>
                </tr>
                <tr>
                    <td class="label">IMC</td>
                    <td class="value">{{ $consultation->vitalSign?->bmi ?? '—' }}</td>
                    <td class="label">Tension artérielle</td>
                    <td class="value">{{ $consultation->vitalSign?->blood_pressure ?? '—' }}</td>
                    <td class="label">Fréq. cardiaque</td>
                    <td class="value">{{ $consultation->vitalSign?->heart_rate !== null ? $consultation->vitalSign->heart_rate.' bpm' : '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Saturation O₂</td>
                    <td class="value">{{ $consultation->vitalSign?->oxygen_saturation !== null ? $consultation->vitalSign->oxygen_saturation.' %' : '—' }}</td>
                    <td class="label">Fréq. respiratoire</td>
                    <td class="value" colspan="3">{{ $consultation->vitalSign?->respiratory_rate !== null ? $consultation->vitalSign->respiratory_rate.' /min' : '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Examen clinique &amp; diagnostic</div>
        <div class="box-body">
            <div class="html-content">
                <p><strong>Observations cliniques :</strong></p>
                {!! $consultation->clinical_examination ?: '<p>—</p>' !!}
                <p><strong>Diagnostic principal :</strong></p>
                {!! $consultation->diagnosis ?: '<p>—</p>' !!}
                @if ($consultation->secondary_diagnoses)
                    <p><strong>Diagnostics secondaires :</strong></p>
                    <p>{{ $consultation->secondary_diagnoses }}</p>
                @endif
                @if ($consultation->medical_notes)
                    <p><strong>Notes médicales :</strong></p>
                    {!! $consultation->medical_notes !!}
                @endif
            </div>
        </div>
    </div>

    @if ($consultation->treatment_plan || $consultation->recommendations || $consultation->follow_up_date)
        <div class="box">
            <div class="box-title">Traitement &amp; recommandations</div>
            <div class="box-body">
                <div class="html-content">
                    @if ($consultation->treatment_plan)
                        <p><strong>Plan thérapeutique :</strong></p>
                        {!! $consultation->treatment_plan !!}
                    @endif
                    @if ($consultation->recommendations)
                        <p><strong>Recommandations :</strong></p>
                        {!! $consultation->recommendations !!}
                    @endif
                    @if ($consultation->follow_up_date)
                        <p><strong>Prochain contrôle :</strong> {{ $consultation->follow_up_date->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="footer">
        Document généré par DOCTA — {{ $consultation->created_at?->format('d/m/Y H:i') }} — Signature du médecin
    </div>
</body>
</html>
