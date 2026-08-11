<?php

namespace App\Services;

use App\Enums\PrescriptionStatus;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrescriptionService
{
    /**
     * Génère un numéro d'ordonnance unique (ORD-000001).
     */
    public function generateNumber(): string
    {
        $sequence = Prescription::withTrashed()->max('id') ?? 0;

        return 'ORD-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crée une ordonnance avec ses médicaments.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(array $data, array $items, ?User $actor = null): Prescription
    {
        $actor ??= auth()->user();

        $this->authorizeDoctorFor($data['doctor_id'] ?? null, $actor);
        abort_if($items === [], 422, 'Une ordonnance doit contenir au moins un médicament.');

        return DB::transaction(function () use ($data, $items, $actor): Prescription {
            $prescription = Prescription::create([
                'prescription_number' => $this->generateNumber(),
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'consultation_id' => $data['consultation_id'] ?? null,
                'prescription_date' => $data['prescription_date'],
                'status' => PrescriptionStatus::Draft->value,
                'notes' => $data['notes'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'verification_token' => Str::random(40),
                'created_by' => $actor?->id,
            ]);

            $this->syncItems($prescription, $items);

            return $prescription->load('items');
        });
    }

    /**
     * Modifie une ordonnance brouillon (contenu + médicaments).
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function update(Prescription $prescription, array $data, array $items, ?User $actor = null): Prescription
    {
        $actor ??= auth()->user();

        abort_unless($prescription->isEditable(), 409, 'Une ordonnance émise, annulée ou expirée ne peut plus être modifiée.');
        abort_if($items === [], 422, 'Une ordonnance doit contenir au moins un médicament.');

        $this->authorizeDoctorFor($data['doctor_id'] ?? $prescription->doctor_id, $actor);

        return DB::transaction(function () use ($prescription, $data, $items, $actor): Prescription {
            $prescription->fill([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'consultation_id' => $data['consultation_id'] ?? null,
                'prescription_date' => $data['prescription_date'],
                'notes' => $data['notes'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
            ]);
            $prescription->save();

            $this->syncItems($prescription, $items);

            activity('prescriptions')
                ->performedOn($prescription)
                ->causedBy($actor)
                ->withProperties(['prescription_number' => $prescription->prescription_number])
                ->log('Ordonnance modifiée');

            return $prescription->load('items');
        });
    }

    /**
     * Émet une ordonnance brouillon.
     */
    public function issue(Prescription $prescription, ?User $actor = null): Prescription
    {
        $actor ??= auth()->user();

        abort_unless($prescription->isEditable(), 409, 'Seule une ordonnance brouillon peut être émise.');

        $itemsCount = $prescription->items()->count();

        abort_if($itemsCount === 0, 422, 'Impossible d\'émettre une ordonnance vide.');

        $prescription->forceFill([
            'status' => PrescriptionStatus::Issued->value,
            'verification_token' => $prescription->verification_token ?? Str::random(40),
        ])->save();

        activity('prescriptions')
            ->performedOn($prescription)
            ->causedBy($actor)
            ->withProperties(['prescription_number' => $prescription->prescription_number])
            ->log('Ordonnance émise');

        return $prescription->load('items');
    }

    /**
     * Annule une ordonnance (brouillon ou émise).
     */
    public function cancel(Prescription $prescription, ?string $reason = null, ?User $actor = null): Prescription
    {
        $actor ??= auth()->user();

        abort_if(in_array($prescription->status, [PrescriptionStatus::Cancelled, PrescriptionStatus::Expired], true), 409, 'Cette ordonnance est déjà clôturée.');

        $prescription->forceFill(['status' => PrescriptionStatus::Cancelled->value])->save();

        activity('prescriptions')
            ->performedOn($prescription)
            ->causedBy($actor)
            ->withProperties([
                'prescription_number' => $prescription->prescription_number,
                'reason' => $reason,
            ])
            ->log('Ordonnance annulée');

        return $prescription->fresh();
    }

    /**
     * Duplique une ordonnance en un nouveau brouillon.
     */
    public function duplicate(Prescription $prescription, ?User $actor = null): Prescription
    {
        $actor ??= auth()->user();

        $this->authorizeDoctorFor($prescription->doctor_id, $actor);

        return DB::transaction(function () use ($prescription, $actor): Prescription {
            $copy = Prescription::create([
                'prescription_number' => $this->generateNumber(),
                'patient_id' => $prescription->patient_id,
                'doctor_id' => $prescription->doctor_id,
                'consultation_id' => $prescription->consultation_id,
                'prescription_date' => now()->toDateString(),
                'status' => PrescriptionStatus::Draft->value,
                'notes' => $prescription->notes,
                'valid_until' => $prescription->valid_until,
                'verification_token' => Str::random(40),
                'created_by' => $actor?->id,
            ]);

            $copyItems = $prescription->items()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($item): array => $item->only([
                    'medicine_name', 'active_ingredient', 'dosage', 'form', 'route',
                    'frequency', 'duration', 'duration_unit', 'quantity', 'instructions', 'notes', 'sort_order',
                ]))
                ->all();

            $this->syncItems($copy, $copyItems);

            activity('prescriptions')
                ->performedOn($copy)
                ->causedBy($actor)
                ->withProperties([
                    'prescription_number' => $copy->prescription_number,
                    'source_number' => $prescription->prescription_number,
                ])
                ->log('Ordonnance dupliquée');

            return $copy->load('items');
        });
    }

    /**
     * Fait passer les ordonnances émises expirées au statut « Expirée ».
     */
    public function expireOverdue(): int
    {
        return Prescription::expireOverdue();
    }

    /**
     * Remplace les médicaments d'une ordonnance en conservant l'ordre.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(Prescription $prescription, array $items): void
    {
        $normalized = [];

        foreach (array_values($items) as $index => $item) {
            $normalized[] = [
                ...$item,
                'sort_order' => $index + 1,
            ];
        }

        $prescription->items()->delete();
        $prescription->items()->createMany($normalized);
    }

    /**
     * Un médecin ne peut créer une ordonnance qu'à son propre nom,
     * sauf autorisation explicite (administrateur).
     */
    public function authorizeDoctorFor(?int $doctorId, ?User $actor): void
    {
        if (! $actor || ! $actor->hasRole('doctor')) {
            return;
        }

        if ($actor->isAdmin()) {
            return;
        }

        $actorDoctorId = Doctor::query()->where('user_id', $actor->id)->value('id');

        abort_if($doctorId !== $actorDoctorId, 403, 'Un médecin ne peut pas créer une ordonnance au nom d\'un autre médecin.');
    }
}
