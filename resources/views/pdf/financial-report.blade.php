<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport financier</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #111827; line-height: 1.5; }
        .header { display: table; width: 100%; margin-bottom: 18px; border-bottom: 3px solid #0284c7; padding-bottom: 10px; }
        .brand { font-size: 24px; font-weight: bold; color: #0284c7; }
        .brand small { display: block; font-size: 11px; font-weight: normal; color: #4b5563; }
        .cabinet-info { text-align: right; font-size: 11px; color: #374151; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        .subtitle { color: #6b7280; font-size: 11px; margin-bottom: 16px; }
        .stats { display: table; width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 12px -8px 20px; }
        .stat { display: table-cell; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; text-align: center; }
        .stat .num { font-size: 20px; font-weight: bold; color: #0284c7; }
        .stat .lbl { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; }
        .box { border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 14px; }
        .box-title { background: #f3f4f6; padding: 6px 10px; font-weight: bold; font-size: 12px; color: #374151; border-bottom: 1px solid #e5e7eb; }
        .box-body { padding: 10px; }
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid th { background: #f3f4f6; text-align: left; font-size: 11px; color: #374151; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        table.grid td { padding: 6px 8px; font-size: 11px; border-bottom: 1px solid #f3f4f6; }
        table.grid td.num, table.grid th.num { text-align: right; }
        .empty { color: #9ca3af; font-style: italic; }
        .footer { margin-top: 24px; font-size: 10px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand" style="display:inline-block;">
            {{ $cabinet['cabinet_name'] ?? 'DOCTA' }}
            <small>Logiciel de gestion de cabinet médical</small>
        </div>
        <div class="cabinet-info" style="display:inline-block; float:right;">
            <strong>{{ $cabinet['cabinet_name'] ?? 'Cabinet Médical' }}</strong><br>
            {{ $cabinet['cabinet_address'] ?? '—' }}<br>
            @if (($cabinet['cabinet_phone'] ?? null)) Tél : {{ $cabinet['cabinet_phone'] }} @endif
        </div>
    </div>

    <h1>Rapport financier</h1>
    <div class="subtitle">
        Établi le {{ now()->format('d/m/Y à H:i') }}
        @if (($scope['period'] ?? null)) — Période : {{ $scope['period'] }} @endif
    </div>

    <div class="stats">
        <div class="stat"><div class="lbl">Facturé (cumul)</div><div class="num">{{ number_format((float) $overview['billed'], 3, ',', ' ') }} DT</div></div>
        <div class="stat"><div class="lbl">Encaissé (cumul)</div><div class="num">{{ number_format((float) $overview['collected'], 3, ',', ' ') }} DT</div></div>
        <div class="stat"><div class="lbl">Restant dû</div><div class="num">{{ number_format((float) $overview['outstanding'], 3, ',', ' ') }} DT</div></div>
        <div class="stat"><div class="lbl">Factures émises</div><div class="num">{{ $overview['invoices'] }}</div></div>
        <div class="stat"><div class="lbl">Payées</div><div class="num">{{ $overview['paid_invoices'] }}</div></div>
        <div class="stat"><div class="lbl">En retard</div><div class="num">{{ $overview['overdue_invoices'] }}</div></div>
    </div>

    <div class="box">
        <div class="box-title">Mois en cours (facturé / encaissé)</div>
        <div class="box-body">
            Facturé : <strong>{{ number_format((float) $monthly['billed'], 3, ',', ' ') }} DT</strong>
            — Encaissé : <strong>{{ number_format((float) $monthly['collected'], 3, ',', ' ') }} DT</strong>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Encaissements du jour ({{ $daily['date']?->format('d/m/Y') ?? '—' }}) — total : {{ number_format((float) $daily['total'], 3, ',', ' ') }} DT</div>
        <div class="box-body">
            @if ($daily['payments']->isNotEmpty())
                <table class="grid">
                    <thead>
                        <tr>
                            <th>N° paiement</th>
                            <th>Patient</th>
                            <th>Facture</th>
                            <th>Moyen</th>
                            <th class="num">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daily['payments'] as $payment)
                            <tr>
                                <td>{{ $payment->payment_number }}</td>
                                <td>{{ $payment->invoice?->patient?->full_name ?? '—' }}</td>
                                <td>{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                                <td>{{ $payment->paymentMethod?->name ?? '—' }}</td>
                                <td class="num">{{ number_format((float) $payment->amount, 3, ',', ' ') }} DT</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <span class="empty">Aucun encaissement aujourd'hui.</span>
            @endif
        </div>
    </div>

    <div class="box">
        <div class="box-title">Factures en retard ({{ $overdue->count() }})</div>
        <div class="box-body">
            @if ($overdue->isNotEmpty())
                <table class="grid">
                    <thead>
                        <tr>
                            <th>N° facture</th>
                            <th>Patient</th>
                            <th>Échéance</th>
                            <th class="num">Total</th>
                            <th class="num">Restant dû</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($overdue as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->patient?->full_name ?? '—' }}</td>
                                <td>{{ $invoice->due_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="num">{{ number_format((float) $invoice->total, 3, ',', ' ') }} DT</td>
                                <td class="num">{{ number_format((float) $invoice->amount_remaining, 3, ',', ' ') }} DT</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <span class="empty">Aucune facture en retard.</span>
            @endif
        </div>
    </div>

    <div class="footer">Document généré par DOCTA — ERP Médical.</div>
</body>
</html>
