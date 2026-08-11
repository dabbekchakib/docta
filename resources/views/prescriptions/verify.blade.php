<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification d'ordonnance — DOCTA</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Helvetica', 'Arial', sans-serif; background: #f8fafc; color: #111827; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; max-width: 480px; width: 90%; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .brand { font-size: 22px; font-weight: bold; color: #0284c7; margin-bottom: 4px; }
        .brand small { display: block; font-size: 11px; color: #6b7280; font-weight: normal; }
        h1 { font-size: 16px; margin: 18px 0 12px; }
        .verified { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 600; }
        .ok { background: #d1fae5; color: #065f46; }
        .ko { background: #fee2e2; color: #991b1b; }
        .info { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .info td { padding: 8px 0; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
        .info td.label { color: #6b7280; width: 45%; }
        .info td.value { font-weight: 600; }
        .back { display: block; margin-top: 20px; text-align: center; font-size: 13px; color: #0284c7; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            DOCTA
            <small>Vérification sécurisée d'ordonnance</small>
        </div>

        @if ($prescription->status->value === 'issued')
            <h1>Ordonnance valide</h1>
            <span class="verified ok">Document authentifié</span>
        @else
            <h1>Ordonnance {{ $prescription->status->label() }}</h1>
            <span class="verified ko">Document {{ $prescription->status->label() }}</span>
        @endif

        <table class="info">
            <tr>
                <td class="label">Ordonnance N°</td>
                <td class="value">{{ $prescription->prescription_number }}</td>
            </tr>
            <tr>
                <td class="label">Date</td>
                <td class="value">{{ $prescription->prescription_date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Médecin</td>
                <td class="value">{{ $prescription->doctor?->full_name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Patient</td>
                <td class="value">{{ $prescription->patient?->full_name ?? '—' }}</td>
            </tr>
            @if ($prescription->valid_until)
                <tr>
                    <td class="label">Valable jusqu'au</td>
                    <td class="value">{{ $prescription->valid_until->format('d/m/Y') }}</td>
                </tr>
            @endif
        </table>

        <p style="font-size:11px; color:#9ca3af; margin-top:16px;">
            Le QR Code contient uniquement un identifiant sécurisé. Aucune donnée médicale n'est transmise.
        </p>

        <a class="back" href="{{ \Filament\Facades\Filament::getPanel('admin')->getUrl() }}">Retour au tableau de bord</a>
    </div>
</body>
</html>
