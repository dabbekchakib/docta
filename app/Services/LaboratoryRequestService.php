<?php

namespace App\Services;

use App\Enums\LaboratoryRequestStatus;
use App\Models\Doctor;
use App\Models\LaboratoryRequest;
use App\Models\Sample;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LaboratoryRequestService
{
    /**
     * Génère un numéro de demande unique (LAB-000001).
     */
    public function generateNumber(): string
    {
        $sequence = LaboratoryRequest::withTrashed()->max('id') ?? 0;

        return 'LAB-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Génère un numéro de prélèvement unique (ECH-000001).
     */
    public function generateSampleNumber(): string
    {
        $sequence = Sample::withTrashed()->max('id') ?? 0;

        return 'ECH-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crée une demande d'examen avec ses examens.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(array $data, array $items, ?User $actor = null): LaboratoryRequest
    {
        $actor ??= auth()->user();

        $this->authorizeDoctorFor($data['doctor_id'] ?? null, $actor);
        abort_if($items === [], 422, 'Une demande d\'examen doit contenir au moins un examen.');

        return DB::transaction(function () use ($data, $items, $actor): LaboratoryRequest {
            $request = LaboratoryRequest::create([
                'request_number' => $this->generateNumber(),
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'consultation_id' => $data['consultation_id'] ?? null,
                'laboratory_id' => $data['laboratory_id'] ?? null,
                'requested_at' => $data['requested_at'],
                'priority' => $data['priority'] ?? 'normal',
                'status' => LaboratoryRequestStatus::Draft->value,
                'clinical_information' => $data['clinical_information'] ?? null,
                'doctor_notes' => $data['doctor_notes'] ?? null,
                'patient_instructions' => $data['patient_instructions'] ?? null,
                'created_by' => $actor?->id,
            ]);

            $this->syncItems($request, $items);

            activity('laboratory_requests')
                ->performedOn($request)
                ->causedBy($actor)
                ->withProperties(['request_number' => $request->request_number])
                ->log('Demande d\'examen créée');

            return $request->load('items.test', 'patient', 'doctor', 'laboratory');
        });
    }

    /**
     * Modifie une demande brouillon (contenu + examens).
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function update(LaboratoryRequest $request, array $data, array $items, ?User $actor = null): LaboratoryRequest
    {
        $actor ??= auth()->user();

        abort_unless($request->isEditable(), 409, 'Une demande transmise ou clôturée ne peut plus être modifiée.');
        abort_if($items === [], 422, 'Une demande d\'examen doit contenir au moins un examen.');

        $this->authorizeDoctorFor($data['doctor_id'] ?? $request->doctor_id, $actor);

        return DB::transaction(function () use ($request, $data, $items, $actor): LaboratoryRequest {
            $request->fill([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'consultation_id' => $data['consultation_id'] ?? null,
                'laboratory_id' => $data['laboratory_id'] ?? null,
                'requested_at' => $data['requested_at'],
                'priority' => $data['priority'] ?? 'normal',
                'clinical_information' => $data['clinical_information'] ?? null,
                'doctor_notes' => $data['doctor_notes'] ?? null,
                'patient_instructions' => $data['patient_instructions'] ?? null,
            ]);
            $request->save();

            $this->syncItems($request, $items);

            activity('laboratory_requests')
                ->performedOn($request)
                ->causedBy($actor)
                ->withProperties(['request_number' => $request->request_number])
                ->log('Demande d\'examen modifiée');

            return $request->load('items.test', 'patient', 'doctor', 'laboratory');
        });
    }

    /**
     * Transmet une demande brouillon au laboratoire.
     */
    public function submit(LaboratoryRequest $request, ?User $actor = null): LaboratoryRequest
    {
        $actor ??= auth()->user();

        abort_unless($request->status === LaboratoryRequestStatus::Draft, 409, 'Seule une demande brouillon peut être transmise.');
        abort_if($request->items()->count() === 0, 422, 'Impossible de transmettre une demande vide.');

        $request->forceFill(['status' => LaboratoryRequestStatus::Requested->value])->save();

        activity('laboratory_requests')
            ->performedOn($request)
            ->causedBy($actor)
            ->withProperties(['request_number' => $request->request_number])
            ->log('Demande d\'examen transmise au laboratoire');

        return $request->fresh();
    }

    /**
     * Accepte une demande pour prise en charge laboratoire.
     */
    public function accept(LaboratoryRequest $request, ?User $actor = null): LaboratoryRequest
    {
        $actor ??= auth()->user();

        abort_unless($request->status === LaboratoryRequestStatus::Requested, 409, 'Seule une demande transmise peut être acceptée.');

        $request->forceFill(['status' => LaboratoryRequestStatus::Accepted->value])->save();

        activity('laboratory_requests')
            ->performedOn($request)
            ->causedBy($actor)
            ->withProperties(['request_number' => $request->request_number])
            ->log('Demande d\'examen acceptée par le laboratoire');

        return $request->fresh();
    }

    /**
     * Annule une demande (sauf si déjà annulée ou terminée).
     */
    public function cancel(LaboratoryRequest $request, ?string $reason = null, ?User $actor = null): LaboratoryRequest
    {
        $actor ??= auth()->user();

        abort_if(in_array($request->status, [LaboratoryRequestStatus::Cancelled, LaboratoryRequestStatus::Completed], true), 409, 'Cette demande est déjà clôturée.');

        $request->forceFill(['status' => LaboratoryRequestStatus::Cancelled->value])->save();

        activity('laboratory_requests')
            ->performedOn($request)
            ->causedBy($actor)
            ->withProperties([
                'request_number' => $request->request_number,
                'reason' => $reason,
            ])
            ->log('Demande d\'examen annulée');

        return $request->fresh();
    }

    /**
     * Enregistre un prélèvement pour un examen de la demande.
     */
    public function collectSample(LaboratoryRequest $request, int $itemId, ?string $notes, ?User $actor = null): Sample
    {
        $actor ??= auth()->user();

        abort_if($request->isValidated() || $request->status === LaboratoryRequestStatus::Cancelled, 409, 'Impossible de prélever une demande clôturée.');

        $item = $request->items()->findOrFail($itemId);

        $sample = Sample::create([
            'laboratory_request_id' => $request->id,
            'laboratory_request_item_id' => $item->id,
            'sample_number' => $this->generateSampleNumber(),
            'sample_type' => $item->sample_type->value,
            'collected_at' => now(),
            'collected_by' => $actor?->id,
            'received_at' => null,
            'status' => 'collected',
            'rejection_reason' => null,
            'notes' => $notes,
        ]);

        $item->forceFill(['status' => 'sampled'])->save();

        if (! in_array($request->status, [LaboratoryRequestStatus::SampleCollected, LaboratoryRequestStatus::InAnalysis, LaboratoryRequestStatus::ResultsEntered], true)) {
            $request->forceFill(['status' => LaboratoryRequestStatus::SampleCollected->value])->save();
        }

        activity('laboratory_requests')
            ->performedOn($request)
            ->causedBy($actor)
            ->withProperties([
                'request_number' => $request->request_number,
                'sample_number' => $sample->sample_number,
            ])
            ->log('Prélèvement effectué');

        return $sample->load('item.test');
    }

    /**
     * Réceptionne tous les prélèvements collectés d'une demande.
     */
    public function receiveSamples(LaboratoryRequest $request, ?User $actor = null): int
    {
        $actor ??= auth()->user();

        $updated = $request->samples()
            ->where('status', 'collected')
            ->update([
                'status' => 'received',
                'received_at' => now(),
            ]);

        if ($updated > 0) {
            activity('laboratory_requests')
                ->performedOn($request)
                ->causedBy($actor)
                ->withProperties(['request_number' => $request->request_number])
                ->log('Prélèvements réceptionnés');
        }

        return $updated;
    }

    /**
     * Rejette un prélèvement avec un motif.
     */
    public function rejectSample(Sample $sample, string $reason, ?User $actor = null): Sample
    {
        $actor ??= auth()->user();

        abort_if($sample->status === 'rejected', 409, 'Ce prélèvement est déjà rejeté.');

        $sample->forceFill([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ])->save();

        activity('laboratory_requests')
            ->performedOn($sample->request)
            ->causedBy($actor)
            ->withProperties([
                'request_number' => $sample->request?->request_number,
                'sample_number' => $sample->sample_number,
                'rejection_reason' => $reason,
            ])
            ->log('Prélèvement rejeté');

        return $sample->fresh();
    }

    /**
     * Marque tous les prélèvements reçus comme traités (analyse en cours).
     */
    public function processSamples(LaboratoryRequest $request, ?User $actor = null): int
    {
        $actor ??= auth()->user();

        $updated = $request->samples()
            ->where('status', 'received')
            ->update(['status' => 'processed']);

        if ($updated > 0) {
            $request->forceFill(['status' => LaboratoryRequestStatus::InAnalysis->value])->save();

            activity('laboratory_requests')
                ->performedOn($request)
                ->causedBy($actor)
                ->withProperties(['request_number' => $request->request_number])
                ->log('Prélèvements traités — analyse en cours');
        }

        return $updated;
    }

    /**
     * Remplace les examens d'une demande en conservant l'ordre.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(LaboratoryRequest $request, array $items): void
    {
        $normalized = [];

        foreach (array_values($items) as $index => $item) {
            $normalized[] = [
                'laboratory_test_id' => $item['laboratory_test_id'],
                'sample_type' => $item['sample_type'] ?? 'blood',
                'instructions' => $item['instructions'] ?? null,
                'notes' => $item['notes'] ?? null,
                'status' => 'pending',
                'sort_order' => $index + 1,
            ];
        }

        $request->items()->delete();
        $request->items()->createMany($normalized);
    }

    /**
     * Un médecin ne peut créer une demande qu'à son propre nom,
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

        abort_if($doctorId !== $actorDoctorId, 403, 'Un médecin ne peut pas créer une demande d\'examen au nom d\'un autre médecin.');
    }
}
