<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Enums\PatientStatus;
use App\Enums\LaboratoryRequestStatus;
use App\Filament\Resources\Consultations\ConsultationResource;
use App\Filament\Resources\CreditNotes\CreditNoteResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\LaboratoryRequests\LaboratoryRequestResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Receipts\ReceiptResource;
use App\Filament\Resources\Refunds\RefundResource;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\Patient;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\HasLabel;
use Spatie\Activitylog\Models\Activity;

class PatientView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                View::make('filament.infolists.dmp-alerts')
                    ->columnSpanFull()
                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()?->medicalRecord]),
                Tabs::make('fiche_patient')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informations générales')
                            ->schema([
                                Section::make('Identité')
                                    ->schema([
                                        TextEntry::make('patient_number')->label('N° dossier'),
                                        TextEntry::make('full_name')->label('Nom complet'),
                                        TextEntry::make('title')->label('Civilité')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('gender')->label('Sexe')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('birth_date')->label('Date de naissance')->date('d/m/Y'),
                                        TextEntry::make('age')->label('Âge')
                                            ->formatStateUsing(fn (?int $state): string => $state ? $state.' ans' : '—'),
                                        TextEntry::make('cin')->label('CIN / Passeport')->placeholder('—'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn (PatientStatus $state): string => $state->getColor()),
                                    ])
                                    ->columns(3),
                                Section::make('Coordonnées')
                                    ->schema([
                                        TextEntry::make('phone')->label('Téléphone principal'),
                                        TextEntry::make('phone_secondary')->label('Téléphone secondaire')->placeholder('—'),
                                        TextEntry::make('email')->label('Adresse email')->placeholder('—'),
                                        TextEntry::make('address')->label('Adresse')->placeholder('—'),
                                        TextEntry::make('city')->label('Ville')->placeholder('—'),
                                        TextEntry::make('governorate')->label('Gouvernorat')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('postal_code')->label('Code postal')->placeholder('—'),
                                    ])
                                    ->columns(2),
                                Section::make('Assurance')
                                    ->schema([
                                        TextEntry::make('has_cnam')->label('Affilié CNAM')
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Oui' : 'Non'),
                                        TextEntry::make('cnam_number')->label('N° CNAM')->placeholder('—'),
                                        TextEntry::make('has_insurance')->label('Assurance privée')
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Oui' : 'Non'),
                                        TextEntry::make('insurance_number')->label('N° assuré')->placeholder('—'),
                                        TextEntry::make('insurance_expires_at')->label('Expiration')->date('d/m/Y')->placeholder('—'),
                                    ])
                                    ->columns(2),
                                Section::make('Contact d\'urgence')
                                    ->schema([
                                        TextEntry::make('emergency_contact')->label('Nom')->placeholder('—'),
                                        TextEntry::make('emergency_relation')->label('Lien de parenté')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('emergency_phone')->label('Téléphone')->placeholder('—'),
                                        TextEntry::make('emergency_address')->label('Adresse')->placeholder('—'),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Rendez-vous')
                            ->schema([
                                RepeatableEntry::make('appointments')
                                    ->label('Historique des rendez-vous')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveAppointments($component))
                                    ->schema([
                                        TextEntry::make('appointment_date')->label('Date'),
                                        TextEntry::make('start_time')->label('Heure'),
                                        TextEntry::make('doctor.full_name')->label('Médecin'),
                                        TextEntry::make('type')->label('Type')
                                            ->formatStateUsing(fn ($state): string => self::enumLabel($state)),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn ($state): string => $state->getColor()),
                                    ])
                                    ->columns(3),
                            ]),
                        Tab::make('Consultations')
                            ->schema([
                                RepeatableEntry::make('consultations')
                                    ->label('Historique des consultations')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveConsultations($component))
                                    ->schema([
                                        TextEntry::make('consultation_date')->label('Date'),
                                        TextEntry::make('doctor.full_name')->label('Médecin'),
                                        TextEntry::make('reason')->label('Motif'),
                                        TextEntry::make('diagnosis')->label('Diagnostic'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn ($state): string => $state->getColor()),
                                        TextEntry::make('open')->label('')
                                            ->formatStateUsing(fn (): string => 'Ouvrir')
                                            ->color('primary')
                                            ->url(fn (TextEntry $component): ?string => self::consultationUrl($component)),
                                    ])
                                    ->columns(3),
                            ]),
                        Tab::make('Examens')
                            ->schema([
                                RepeatableEntry::make('laboratory_requests')
                                    ->label('Historique des examens biologiques')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveLaboratoryRequests($component))
                                    ->schema([
                                        TextEntry::make('request_number')->label('N° demande'),
                                        TextEntry::make('requested_at')->label('Date'),
                                        TextEntry::make('doctor.full_name')->label('Médecin'),
                                        TextEntry::make('tests')->label('Examens'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn ($state): string => self::requestStatusColor($state)),
                                        TextEntry::make('open')->label('')
                                            ->formatStateUsing(fn (): string => 'Ouvrir')
                                            ->color('primary')
                                            ->url(fn (TextEntry $component): ?string => self::laboratoryRequestUrl($component)),
                                    ])
                                    ->columns(4),
                            ]),
                        Tab::make('Facturation')
                            ->schema([
                                Section::make('Solde')
                                    ->description('Solde global du patient (factures non annulées).')
                                    ->schema([
                                        TextEntry::make('balance_total')
                                            ->label('Solde à recouvrer')
                                            ->state(fn (TextEntry $component): string => self::resolveBalance($component)['total'])
                                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                                            ->color(fn (mixed $state): string => (float) $state > 0 ? 'danger' : 'success')
                                            ->weight('bold'),
                                        TextEntry::make('balance_paid')
                                            ->label('Total encaissé')
                                            ->state(fn (TextEntry $component): string => self::resolveBalance($component)['paid'])
                                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                                            ->color('success'),
                                        TextEntry::make('balance_invoices')
                                            ->label('Factures')
                                            ->state(fn (TextEntry $component): string => self::resolveBalance($component)['invoices'])
                                            ->formatStateUsing(fn ($state): string => (int) $state.' facture(s)'),
                                        TextEntry::make('balance_payments')
                                            ->label('Paiements encaissés')
                                            ->state(fn (TextEntry $component): string => self::resolveBalance($component)['payments'])
                                            ->formatStateUsing(fn ($state): string => (int) $state.' paiement(s)'),
                                    ])
                                    ->columns(4),
                                RepeatableEntry::make('invoices')
                                    ->label('Factures')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveInvoices($component))
                                    ->schema([
                                        TextEntry::make('invoice_number')->label('N° facture'),
                                        TextEntry::make('invoice_date')->label('Date'),
                                        TextEntry::make('doctor.full_name')->label('Médecin')->placeholder('—'),
                                        TextEntry::make('total')->label('Total')
                                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                                        TextEntry::make('amount_remaining')->label('Restant dû')
                                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT')
                                            ->color(fn (mixed $state): string => (float) $state > 0 ? 'danger' : 'success'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn ($state): string => self::invoiceStatusColor($state)),
                                        TextEntry::make('open')->label('')
                                            ->formatStateUsing(fn (): string => 'Ouvrir')
                                            ->color('primary')
                                            ->url(fn (TextEntry $component): ?string => self::invoiceUrl($component)),
                                    ])
                                    ->columns(4),
                                RepeatableEntry::make('payments')
                                    ->label('Paiements')
                                    ->state(fn (RepeatableEntry $component): array => self::resolvePayments($component))
                                    ->schema([
                                        TextEntry::make('payment_number')->label('N° paiement'),
                                        TextEntry::make('payment_date')->label('Date'),
                                        TextEntry::make('amount')->label('Montant')
                                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                                        TextEntry::make('payment_method')->label('Mode'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn ($state): string => self::paymentStatusColor($state)),
                                        TextEntry::make('open')->label('')
                                            ->formatStateUsing(fn (): string => 'Ouvrir')
                                            ->color('primary')
                                            ->url(fn (TextEntry $component): ?string => self::paymentUrl($component)),
                                    ])
                                    ->columns(4),
                                RepeatableEntry::make('receipts')
                                    ->label('Reçus')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveReceipts($component))
                                    ->schema([
                                        TextEntry::make('receipt_number')->label('N° reçu'),
                                        TextEntry::make('receipt_date')->label('Date'),
                                        TextEntry::make('amount')->label('Montant')
                                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                                        TextEntry::make('payment_number')->label('Paiement lié'),
                                        TextEntry::make('open')->label('')
                                            ->formatStateUsing(fn (): string => 'Ouvrir')
                                            ->color('primary')
                                            ->url(fn (TextEntry $component): ?string => self::receiptUrl($component)),
                                    ])
                                    ->columns(4),
                                RepeatableEntry::make('credit_notes')
                                    ->label('Avoirs')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveCreditNotes($component))
                                    ->schema([
                                        TextEntry::make('credit_note_number')->label('N° avoir'),
                                        TextEntry::make('credit_note_date')->label('Date'),
                                        TextEntry::make('amount')->label('Montant')
                                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn ($state): string => self::creditNoteStatusColor($state)),
                                        TextEntry::make('open')->label('')
                                            ->formatStateUsing(fn (): string => 'Ouvrir')
                                            ->color('primary')
                                            ->url(fn (TextEntry $component): ?string => self::creditNoteUrl($component)),
                                    ])
                                    ->columns(4),
                                RepeatableEntry::make('refunds')
                                    ->label('Remboursements')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveRefunds($component))
                                    ->schema([
                                        TextEntry::make('refund_number')->label('N° remboursement'),
                                        TextEntry::make('refund_date')->label('Date'),
                                        TextEntry::make('amount')->label('Montant')
                                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', ' ').' DT'),
                                        TextEntry::make('status')->label('Statut')
                                            ->badge()
                                            ->color(fn ($state): string => self::refundStatusColor($state)),
                                        TextEntry::make('open')->label('')
                                            ->formatStateUsing(fn (): string => 'Ouvrir')
                                            ->color('primary')
                                            ->url(fn (TextEntry $component): ?string => self::refundUrl($component)),
                                    ])
                                    ->columns(4),
                            ]),
                        Tab::make('Dossier médical')
                            ->schema([
                                View::make('filament.infolists.dmp-summary')
                                    ->columnSpanFull()
                                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()?->medicalRecord]),
                                View::make('filament.infolists.dmp-timeline')
                                    ->columnSpanFull()
                                    ->viewData(fn (View $component): array => ['medicalRecord' => $component->getRecord()?->medicalRecord]),
                            ]),
                        Tab::make('Journal d\'activité')
                            ->schema([
                                RepeatableEntry::make('activities')
                                    ->label('Activités récentes')
                                    ->state(fn (RepeatableEntry $component): array => self::resolveActivities($component))
                                    ->schema([
                                        TextEntry::make('created_at')->label('Date'),
                                        TextEntry::make('description')->label('Action'),
                                        TextEntry::make('causer.name')->label('Utilisateur'),
                                    ])
                                    ->columns(3),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function resolveActivities(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->activities()
            ->with('causer')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Activity $activity): array => [
                'created_at' => $activity->created_at?->format('d/m/Y H:i') ?? '—',
                'description' => $activity->description,
                'causer' => $activity->causer?->name ?? 'Système',
            ])
            ->all();
    }

    private static function enumLabel(mixed $state): string
    {
        if ($state === null || $state === '') {
            return '—';
        }

        if ($state instanceof HasLabel) {
            return $state->getLabel();
        }

        return (string) $state;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveLaboratoryRequests(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->laboratoryRequests()
            ->with('doctor', 'items.test')
            ->latest('requested_at')
            ->limit(20)
            ->get()
            ->map(fn ($request): array => [
                'id' => $request->id,
                'request_number' => $request->request_number,
                'requested_at' => $request->requested_at?->format('d/m/Y') ?? '—',
                'doctor.full_name' => $request->doctor?->full_name,
                'tests' => $request->items->pluck('test.name')->filter()->implode(', ') ?: '—',
                'status' => $request->status,
            ])
            ->all();
    }

    private static function requestStatusColor(mixed $state): string
    {
        if ($state instanceof LaboratoryRequestStatus) {
            return $state->getColor();
        }

        $status = is_string($state) ? LaboratoryRequestStatus::tryFrom($state) : null;

        return $status?->getColor() ?? 'gray';
    }

    private static function laboratoryRequestUrl(TextEntry $component): ?string
    {
        $item = $component->getRecord();

        if (! is_array($item) || empty($item['id'])) {
            return null;
        }

        return LaboratoryRequestResource::getUrl('view', ['record' => $item['id']]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveAppointments(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->appointments()
            ->with('doctor')
            ->latest('appointment_date')
            ->limit(20)
            ->get()
            ->map(fn ($appointment): array => [
                'appointment_date' => $appointment->appointment_date?->format('d/m/Y') ?? '—',
                'start_time' => $appointment->start_time,
                'doctor.full_name' => $appointment->doctor?->full_name,
                'type' => $appointment->type,
                'status' => $appointment->status,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveConsultations(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->consultations()
            ->with('doctor')
            ->latest('consultation_date')
            ->limit(20)
            ->get()
            ->map(fn (Consultation $consultation): array => [
                'id' => $consultation->id,
                'consultation_date' => $consultation->consultation_date?->format('d/m/Y') ?? '—',
                'doctor.full_name' => $consultation->doctor?->full_name,
                'reason' => $consultation->reason,
                'diagnosis' => $consultation->diagnosis ? strip_tags((string) $consultation->diagnosis) : '—',
                'status' => $consultation->status,
            ])
            ->all();
    }

    private static function consultationUrl(TextEntry $component): ?string
    {
        $item = $component->getRecord();

        if (! is_array($item) || empty($item['id'])) {
            return null;
        }

        return ConsultationResource::getUrl('view', ['record' => $item['id']]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveInvoices(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->invoices()
            ->with('doctor')
            ->latest('invoice_date')
            ->limit(30)
            ->get()
            ->map(fn (Invoice $invoice): array => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date?->format('d/m/Y') ?? '—',
                'doctor.full_name' => $invoice->doctor?->full_name,
                'total' => $invoice->total,
                'amount_remaining' => $invoice->amount_remaining,
                'status' => $invoice->status,
            ])
            ->all();
    }

    private static function invoiceStatusColor(mixed $state): string
    {
        if ($state instanceof \App\Enums\InvoiceStatus) {
            return $state->getColor();
        }

        $status = is_string($state) ? \App\Enums\InvoiceStatus::tryFrom($state) : null;

        return $status?->getColor() ?? 'gray';
    }

    /**
     * @return array<string, string>
     */
    private static function resolveBalance(TextEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return ['total' => '0', 'paid' => '0', 'invoices' => '0', 'payments' => '0'];
        }

        return [
            'total' => (string) $patient->invoices()
                ->whereNotIn('status', ['cancelled'])
                ->sum('amount_remaining'),
            'paid' => (string) $patient->payments()
                ->where('status', 'completed')
                ->sum('amount'),
            'invoices' => (string) $patient->invoices()->count(),
            'payments' => (string) $patient->payments()
                ->where('status', 'completed')
                ->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolvePayments(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->payments()
            ->with('paymentMethod')
            ->latest('payment_date')
            ->limit(20)
            ->get()
            ->map(fn ($payment): array => [
                'id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'payment_date' => $payment->payment_date?->format('d/m/Y') ?? '—',
                'amount' => $payment->amount,
                'payment_method' => $payment->paymentMethod?->name,
                'status' => $payment->status,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveReceipts(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->receipts()
            ->with('payment')
            ->latest('receipt_date')
            ->limit(20)
            ->get()
            ->map(fn ($receipt): array => [
                'id' => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'receipt_date' => $receipt->receipt_date?->format('d/m/Y') ?? '—',
                'amount' => $receipt->amount,
                'payment_number' => $receipt->payment?->payment_number,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveCreditNotes(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->creditNotes()
            ->latest('credit_note_date')
            ->limit(20)
            ->get()
            ->map(fn ($creditNote): array => [
                'id' => $creditNote->id,
                'credit_note_number' => $creditNote->credit_note_number,
                'credit_note_date' => $creditNote->credit_note_date?->format('d/m/Y') ?? '—',
                'amount' => $creditNote->amount,
                'status' => $creditNote->status,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveRefunds(RepeatableEntry $component): array
    {
        $patient = $component->getRecord();

        if (! $patient instanceof Patient) {
            return [];
        }

        return $patient->refunds()
            ->latest('refund_date')
            ->limit(20)
            ->get()
            ->map(fn ($refund): array => [
                'id' => $refund->id,
                'refund_number' => $refund->refund_number,
                'refund_date' => $refund->refund_date?->format('d/m/Y') ?? '—',
                'amount' => $refund->amount,
                'status' => $refund->status,
            ])
            ->all();
    }

    private static function paymentStatusColor(mixed $state): string
    {
        if ($state instanceof \App\Enums\PaymentStatus) {
            return $state->getColor();
        }

        $status = is_string($state) ? \App\Enums\PaymentStatus::tryFrom($state) : null;

        return $status?->getColor() ?? 'gray';
    }

    private static function creditNoteStatusColor(mixed $state): string
    {
        if ($state instanceof \App\Enums\CreditNoteStatus) {
            return $state->getColor();
        }

        $status = is_string($state) ? \App\Enums\CreditNoteStatus::tryFrom($state) : null;

        return $status?->getColor() ?? 'gray';
    }

    private static function refundStatusColor(mixed $state): string
    {
        if ($state instanceof \App\Enums\RefundStatus) {
            return $state->getColor();
        }

        $status = is_string($state) ? \App\Enums\RefundStatus::tryFrom($state) : null;

        return $status?->getColor() ?? 'gray';
    }

    private static function paymentUrl(TextEntry $component): ?string
    {
        $item = $component->getRecord();

        if (! is_array($item) || empty($item['id'])) {
            return null;
        }

        return PaymentResource::getUrl('view', ['record' => $item['id']]);
    }

    private static function receiptUrl(TextEntry $component): ?string
    {
        $item = $component->getRecord();

        if (! is_array($item) || empty($item['id'])) {
            return null;
        }

        return ReceiptResource::getUrl('view', ['record' => $item['id']]);
    }

    private static function creditNoteUrl(TextEntry $component): ?string
    {
        $item = $component->getRecord();

        if (! is_array($item) || empty($item['id'])) {
            return null;
        }

        return CreditNoteResource::getUrl('view', ['record' => $item['id']]);
    }

    private static function refundUrl(TextEntry $component): ?string
    {
        $item = $component->getRecord();

        if (! is_array($item) || empty($item['id'])) {
            return null;
        }

        return RefundResource::getUrl('view', ['record' => $item['id']]);
    }

    private static function invoiceUrl(TextEntry $component): ?string
    {
        $item = $component->getRecord();

        if (! is_array($item) || empty($item['id'])) {
            return null;
        }

        return InvoiceResource::getUrl('view', ['record' => $item['id']]);
    }
}
