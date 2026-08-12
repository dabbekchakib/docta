<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->invoice_number }}</title>
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
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 11px; background: #e0f2fe; color: #075985; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.items th { background: #f3f4f6; text-align: left; font-size: 11px; color: #374151; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        table.items td { padding: 8px; font-size: 11px; vertical-align: top; border-bottom: 1px solid #f3f4f6; }
        table.items td.num, table.items th.num { text-align: right; }
        .totals { margin-top: 12px; text-align: right; }
        .totals table { margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 4px 10px; font-size: 11px; }
        .totals td.label { color: #6b7280; }
        .totals tr.total td { font-size: 14px; font-weight: bold; color: #0284c7; }
        .totals tr.balance td { font-weight: bold; }
        .empty { color: #9ca3af; font-style: italic; }
        .footer { margin-top: 28px; display: table; width: 100%; font-size: 10px; color: #6b7280; }
        .footer .signature { width: 50%; text-align: center; }
        .footer .signature .line { border-top: 1px solid #9ca3af; margin-top: 60px; padding-top: 4px; font-size: 11px; color: #4b5563; }
        .amount-label { color: #6b7280; }
    </style>
</head>
<body>
    @php
        $patient = $invoice->patient;
        $doctor = $invoice->doctor;
        $status = $invoice->status;
        $cabinet = $cabinet ?? [];
        $currency = 'DT';
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
            @if (($cabinet['cabinet_email'] ?? null)) Email : {{ $cabinet['cabinet_email'] }}<br> @endif
            @if (($cabinet['cabinet_fiscal_number'] ?? null)) Matricule fiscal : {{ $cabinet['cabinet_fiscal_number'] }} @endif
        </div>
    </div>

    <h1>Facture</h1>
    <div class="subtitle">
        N° {{ $invoice->invoice_number }}
        — Date : {{ $invoice->invoice_date?->format('d/m/Y') ?? '—' }}
        @if ($invoice->due_date)
            — Échéance : {{ $invoice->due_date->format('d/m/Y') }}
        @endif
        <span class="badge {{ match ($status) {
            App\Enums\InvoiceStatus::Paid => 'badge-success',
            App\Enums\InvoiceStatus::Overdue, App\Enums\InvoiceStatus::Cancelled => 'badge-danger',
            App\Enums\InvoiceStatus::PartiallyPaid => 'badge-warning',
            default => '',
        } }}">{{ $status->label() }}</span>
    </div>

    <div class="box">
        <div class="box-title">Émetteur</div>
        <div class="box-body">
            <table class="grid">
                <tr>
                    <td class="label">Cabinet</td>
                    <td class="value">{{ $cabinet['cabinet_name'] ?? '—' }}</td>
                    <td class="label">Matricule fiscal</td>
                    <td class="value">{{ $cabinet['cabinet_fiscal_number'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Adresse</td>
                    <td class="value" colspan="3">{{ $cabinet['cabinet_address'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Téléphone</td>
                    <td class="value">{{ $cabinet['cabinet_phone'] ?? '—' }}</td>
                    <td class="label">Email</td>
                    <td class="value">{{ $cabinet['cabinet_email'] ?? '—' }}</td>
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
                    <td class="label">CIN</td>
                    <td class="value">{{ $patient?->cin ?? '—' }}</td>
                    <td class="label">Téléphone</td>
                    <td class="value">{{ $patient?->phone ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Assurance</td>
                    <td class="value">{{ $patient?->insurance?->getLabel() ?? '—' }} @if ($patient?->cnam_number) ({{ $patient->cnam_number }}) @endif</td>
                    <td class="label">Médecin</td>
                    <td class="value">{{ $doctor?->full_name ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Détail des prestations</div>
        <div class="box-body">
            @if ($invoice->items->isNotEmpty())
                <table class="items">
                    <thead>
                        <tr>
                            <th style="width:30px;">#</th>
                            <th>Désignation</th>
                            <th class="num">Qté</th>
                            <th class="num">Prix unitaire</th>
                            <th class="num">TVA</th>
                            <th class="num">Total HT</th>
                            <th class="num">Total TTC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="num">{{ number_format((float) $item->quantity, 3, ',', ' ') }}</td>
                                <td class="num">{{ number_format((float) $item->unit_price, 3, ',', ' ') }} {{ $currency }}</td>
                                <td class="num">{{ number_format((float) $item->tax_rate, 2, ',', ' ') }} %</td>
                                <td class="num">{{ number_format((float) $item->line_total, 3, ',', ' ') }} {{ $currency }}</td>
                                <td class="num">{{ number_format((float) ($item->line_total + $item->tax_amount), 3, ',', ' ') }} {{ $currency }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="totals">
                    <table>
                        <tr>
                            <td class="label">Sous-total TTC</td>
                            <td class="num">{{ number_format((float) $invoice->subtotal, 3, ',', ' ') }} {{ $currency }}</td>
                        </tr>
                        @if ((float) $invoice->discount_amount > 0)
                            <tr>
                                <td class="label">Remise ({{ $invoice->discount_type === 'percent' ? $invoice->discount_value.' %' : number_format((float) $invoice->discount_value, 3, ',', ' ').' '.$currency }})</td>
                                <td class="num">- {{ number_format((float) $invoice->discount_amount, 3, ',', ' ') }} {{ $currency }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label">Base imposable</td>
                            <td class="num">{{ number_format((float) $invoice->taxable_base, 3, ',', ' ') }} {{ $currency }}</td>
                        </tr>
                        <tr>
                            <td class="label">TVA</td>
                            <td class="num">{{ number_format((float) $invoice->tax_amount, 3, ',', ' ') }} {{ $currency }}</td>
                        </tr>
                        <tr class="total">
                            <td class="label">Total TTC</td>
                            <td class="num">{{ number_format((float) $invoice->total, 3, ',', ' ') }} {{ $currency }}</td>
                        </tr>
                        @if ((float) $invoice->amount_paid > 0)
                            <tr>
                                <td class="label">Montant encaissé</td>
                                <td class="num">{{ number_format((float) $invoice->amount_paid, 3, ',', ' ') }} {{ $currency }}</td>
                            </tr>
                            <tr class="balance">
                                <td class="label">Restant dû</td>
                                <td class="num">{{ number_format((float) $invoice->amount_remaining, 3, ',', ' ') }} {{ $currency }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            @else
                <span class="empty">Aucune prestation enregistrée sur cette facture.</span>
            @endif
        </div>
    </div>

    @if ($invoice->notes)
        <div class="box">
            <div class="box-title">Notes</div>
            <div class="box-body">{{ $invoice->notes }}</div>
        </div>
    @endif

    @if ($invoice->payments->isNotEmpty())
        <div class="box">
            <div class="box-title">Encaissements</div>
            <div class="box-body">
                <table class="items">
                    <thead>
                        <tr>
                            <th>N° paiement</th>
                            <th>Date</th>
                            <th>Moyen de paiement</th>
                            <th class="num">Montant</th>
                            <th>Référence</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->payments->where('status', App\Enums\PaymentStatus::Completed->value) as $payment)
                            <tr>
                                <td>{{ $payment->payment_number }}</td>
                                <td>{{ $payment->payment_date?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $payment->paymentMethod?->name ?? '—' }}</td>
                                <td class="num">{{ number_format((float) $payment->amount, 3, ',', ' ') }} {{ $currency }}</td>
                                <td>{{ $payment->reference ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="footer">
        <div class="signature">
            <div class="line">Date : {{ now()->format('d/m/Y') }} — Cachet et signature du cabinet</div>
        </div>
    </div>
</body>
</html>
