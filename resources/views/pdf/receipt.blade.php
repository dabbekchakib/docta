<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu {{ $receipt->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #111827; line-height: 1.5; }
        .header { display: table; width: 100%; margin-bottom: 18px; border-bottom: 3px solid #0284c7; padding-bottom: 10px; }
        .brand { font-size: 24px; font-weight: bold; color: #0284c7; }
        .brand small { display: block; font-size: 11px; font-weight: normal; color: #4b5563; }
        .cabinet-info { text-align: right; font-size: 11px; color: #374151; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        .subtitle { color: #6b7280; font-size: 11px; margin-bottom: 16px; }
        .box { border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 14px; }
        .box-title { background: #f3f4f6; padding: 6px 10px; font-weight: bold; font-size: 12px; color: #374151; border-bottom: 1px solid #e5e7eb; }
        .box-body { padding: 10px; }
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td { padding: 4px 6px; font-size: 11px; vertical-align: top; }
        table.grid td.label { width: 160px; color: #6b7280; }
        .value { font-weight: 600; }
        .receipt-amount { text-align: center; padding: 24px 0; }
        .receipt-amount .amount { font-size: 26px; font-weight: bold; color: #0284c7; }
        .receipt-amount .label { font-size: 11px; color: #6b7280; }
        .footer { margin-top: 28px; display: table; width: 100%; font-size: 10px; color: #6b7280; }
        .footer .signature { width: 50%; text-align: center; }
        .footer .signature .line { border-top: 1px solid #9ca3af; margin-top: 60px; padding-top: 4px; font-size: 11px; color: #4b5563; }
        .empty { color: #9ca3af; font-style: italic; }
    </style>
</head>
<body>
    @php
        $patient = $receipt->patient;
        $invoice = $receipt->invoice;
        $payment = $receipt->payment;
    @endphp

    <div class="header">
        <div class="brand" style="display:inline-block;">
            {{ $cabinet['cabinet_name'] ?? 'DOCTA' }}
            <small>Logiciel de gestion de cabinet médical</small>
        </div>
        <div class="cabinet-info" style="display:inline-block; float:right;">
            <strong>{{ $cabinet['cabinet_name'] ?? 'Cabinet Médical' }}</strong><br>
            {{ $cabinet['cabinet_address'] ?? '—' }}<br>
            @if (($cabinet['cabinet_phone'] ?? null)) Tél : {{ $cabinet['cabinet_phone'] }}<br> @endif
            @if (($cabinet['cabinet_fiscal_number'] ?? null)) Matricule fiscal : {{ $cabinet['cabinet_fiscal_number'] }} @endif
        </div>
    </div>

    <h1>Reçu</h1>
    <div class="subtitle">
        N° {{ $receipt->receipt_number }}
        — Date : {{ $receipt->receipt_date?->format('d/m/Y') ?? '—' }}
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
                    <td class="label">CIN</td>
                    <td class="value">{{ $patient?->cin ?? '—' }}</td>
                    <td class="label">Téléphone</td>
                    <td class="value">{{ $patient?->phone ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Détail du règlement</div>
        <div class="box-body">
            <table class="grid">
                <tr>
                    <td class="label">Facture</td>
                    <td class="value">{{ $invoice?->invoice_number ?? '—' }}</td>
                    <td class="label">N° paiement</td>
                    <td class="value">{{ $payment?->payment_number ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Moyen de paiement</td>
                    <td class="value">{{ $payment?->paymentMethod?->name ?? '—' }}</td>
                    <td class="label">Référence</td>
                    <td class="value">{{ $payment?->reference ?? '—' }}</td>
                </tr>
            </table>
            <div class="receipt-amount">
                <div class="label">Montant reçu</div>
                <div class="amount">{{ number_format((float) $receipt->amount, 3, ',', ' ') }} {{ $cabinet['currency'] ?? 'DT' }}</div>
            </div>
            @if ($invoice && (float) $invoice->amount_remaining > 0)
                <p style="font-size:11px; color:#374151;">
                    Reste à payer sur la facture {{ $invoice->invoice_number }} :
                    <strong>{{ number_format((float) $invoice->amount_remaining, 3, ',', ' ') }} {{ $cabinet['currency'] ?? 'DT' }}</strong>
                </p>
            @endif
        </div>
    </div>

    @if ($receipt->notes)
        <div class="box">
            <div class="box-title">Notes</div>
            <div class="box-body">{{ $receipt->notes }}</div>
        </div>
    @endif

    <div class="footer">
        <div class="signature">
            <div class="line">Date : {{ now()->format('d/m/Y') }} — Cachet et signature du cabinet</div>
        </div>
    </div>
</body>
</html>
