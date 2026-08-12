<?php

namespace App\Services;

use App\Enums\LaboratoryRequestStatus;
use App\Enums\ResultAbnormality;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryRequestItem;
use App\Models\LaboratoryResult;
use App\Models\LaboratoryResultVersion;
use App\Models\LaboratoryTest;
use App\Models\Patient;
use App\Models\ReferenceRange;
use App\Models\User;
use App\Notifications\LaboratoryResultsAvailableNotification;
use Illuminate\Support\Facades\DB;

class LaboratoryResultService
{
    /**
     * Saisit / remplace les résultats d'une demande.
     * Les résultats validés ne peuvent pas être modifiés silencieusement :
     * toute saisie est refusée dès qu'une validation biologique a eu lieu.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function syncResults(LaboratoryRequest $request, array $rows, ?User $actor = null): int
    {
        $actor ??= auth()->user();

        abort_if($request->isValidated(), 409, 'Les résultats validés ne peuvent plus être modifiés. Utilisez une procédure de correction tracée.');
        abort_if($request->status === LaboratoryRequestStatus::Cancelled, 409, 'Impossible de saisir des résultats sur une demande annulée.');

        $items = $request->items()->with('test')->get()->keyBy('id');

        abort_if($items->isEmpty(), 422, 'La demande ne contient aucun examen.');

        $normalized = [];

        foreach ($rows as $row) {
            $itemId = (int) ($row['laboratory_request_item_id'] ?? 0);
            $item = $items->get($itemId);

            abort_unless($item instanceof LaboratoryRequestItem, 422, 'Examen de résultat invalide pour cette demande.');

            $value = $row['value'] ?? null;
            $numeric = $row['numeric_value'] ?? null;

            if ($numeric === null || $numeric === '') {
                $parsed = filter_var((string) $value, FILTER_VALIDATE_FLOAT);

                $numeric = $parsed !== false ? $parsed : null;
            }

            $unit = $row['unit'] ?? $item->test?->unit;
            $referenceMin = $row['reference_min'] ?? null;
            $referenceMax = $row['reference_max'] ?? null;
            $referenceText = $row['reference_text'] ?? $item->test?->default_reference_value;

            $abnormality = $this->resolveAbnormality(
                $row['abnormality'] ?? null,
                $numeric,
                $referenceMin,
                $referenceMax,
            );

            $normalized[] = [
                'laboratory_request_item_id' => $item->id,
                'parameter_name' => $row['parameter_name'] ?? $item->test?->name,
                'value' => $value,
                'numeric_value' => $numeric,
                'unit' => $unit,
                'reference_min' => $referenceMin,
                'reference_max' => $referenceMax,
                'reference_text' => $referenceText,
                'abnormality' => $abnormality,
                'comment' => $row['comment'] ?? null,
                'resulted_at' => now(),
            ];
        }

        abort_if($normalized === [], 422, 'Aucun résultat à enregistrer.');

        return DB::transaction(function () use ($request, $normalized, $actor): int {
            foreach ($request->items as $item) {
                $item->results()->delete();
            }

            $created = LaboratoryResult::query()->insert($normalized);

            $this->refreshItemStatuses($request);
            $this->refreshRequestStatus($request);

            activity('laboratory_results')
                ->performedOn($request)
                ->causedBy($actor)
                ->withProperties(['request_number' => $request->request_number])
                ->log('Résultats saisis');

            return $created;
        });
    }

    /**
     * Validation biologique : verrouille les résultats et notifie le médecin.
     */
    public function validate(LaboratoryRequest $request, ?User $actor = null): LaboratoryRequest
    {
        $actor ??= auth()->user();

        abort_if($request->isValidated(), 409, 'Les résultats de cette demande sont déjà validés.');
        abort_if($request->status === LaboratoryRequestStatus::Cancelled, 409, 'Impossible de valider une demande annulée.');
        abort_unless($request->results()->exists(), 422, 'Aucun résultat à valider.');
        abort_unless($request->allItemsHaveResults(), 422, 'Tous les examens doivent avoir des résultats avant validation.');

        return DB::transaction(function () use ($request, $actor): LaboratoryRequest {
            $request->results()->update([
                'validated_at' => now(),
                'validated_by' => $actor?->id,
            ]);

            $request->forceFill(['status' => LaboratoryRequestStatus::Validated->value])->save();

            activity('laboratory_results')
                ->performedOn($request)
                ->causedBy($actor)
                ->withProperties(['request_number' => $request->request_number])
                ->log('Résultats validés biologiquement');

            $doctor = $request->doctor;

            if ($doctor?->user) {
                $doctor->user->notify(new LaboratoryResultsAvailableNotification($request));
            }

            return $request->fresh();
        });
    }

    /**
     * Correction tracée d'un résultat validé (architecture de versions).
     * L'ancienne valeur est conservée dans l'historique.
     */
    public function recordCorrection(
        LaboratoryResult $result,
        string $newValue,
        ?float $newNumericValue,
        ?string $reason,
        ?User $actor = null,
    ): LaboratoryResult {
        $actor ??= auth()->user();

        abort_unless($result->isValidated(), 409, 'Seul un résultat validé nécessite une correction tracée.');

        return DB::transaction(function () use ($result, $newValue, $newNumericValue, $reason, $actor): LaboratoryResult {
            LaboratoryResultVersion::create([
                'laboratory_result_id' => $result->id,
                'previous_value' => $result->value,
                'previous_numeric_value' => $result->numeric_value,
                'new_value' => $newValue,
                'new_numeric_value' => $newNumericValue,
                'reason' => $reason,
                'corrected_by' => $actor?->id,
                'corrected_at' => now(),
            ]);

            $result->forceFill([
                'value' => $newValue,
                'numeric_value' => $newNumericValue,
                'abnormality' => $this->resolveAbnormality(
                    null,
                    $newNumericValue,
                    $result->reference_min,
                    $result->reference_max,
                ),
            ])->save();

            activity('laboratory_results')
                ->performedOn($result->item->request)
                ->causedBy($actor)
                ->withProperties([
                    'request_number' => $result->item->request?->request_number,
                    'result_id' => $result->id,
                    'previous_value' => $result->getOriginal('value'),
                    'new_value' => $newValue,
                    'reason' => $reason,
                ])
                ->log('Résultat validé corrigé (version tracée)');

            return $result->fresh();
        });
    }

    /**
     * Résout l'intervalle de référence applicable à un patient pour un examen
     * (selon le sexe puis l'âge), ou l'intervalle générique « all ».
     */
    public function resolveReferenceRange(?LaboratoryTest $test, ?Patient $patient): ?ReferenceRange
    {
        if (! $test) {
            return null;
        }

        $gender = $patient?->gender?->value ?? 'all';
        $age = $patient?->birth_date?->age;

        $genderFirst = $test->referenceRanges()
            ->where(fn ($query) => $query->where('gender', $gender)->orWhere('gender', 'all'))
            ->orderByRaw('CASE WHEN gender = ? THEN 0 ELSE 1 END', [$gender])
            ->orderBy('gender');

        foreach ($genderFirst->get() as $range) {
            if ($range->gender !== 'all' && $range->gender !== $gender) {
                continue;
            }

            if ($age !== null && $range->age_min !== null && $age < $range->age_min) {
                continue;
            }

            if ($age !== null && $range->age_max !== null && $age > $range->age_max) {
                continue;
            }

            return $range;
        }

        return null;
    }

    /**
     * Met à jour le statut de chaque examen selon la présence de résultats.
     */
    private function refreshItemStatuses(LaboratoryRequest $request): void
    {
        foreach ($request->items()->with('results')->get() as $item) {
            $item->forceFill(['status' => $item->results()->exists() ? 'completed' : 'pending'])->save();
        }
    }

    private function refreshRequestStatus(LaboratoryRequest $request): void
    {
        $status = $request->allItemsHaveResults()
            ? LaboratoryRequestStatus::ResultsEntered
            : LaboratoryRequestStatus::InAnalysis;

        $request->forceFill(['status' => $status->value])->save();
    }

    /**
     * Résout l'anomalie : comparaison numérique avec l'intervalle de référence.
     * Aucune interprétation médicale n'est déduite automatiquement ;
     * les libellés critiques / positifs / négatifs restent saisis manuellement.
     */
    private function resolveAbnormality(
        mixed $submitted,
        mixed $numeric,
        mixed $referenceMin,
        mixed $referenceMax,
    ): string {
        if (is_string($submitted) && $submitted !== '' && $submitted !== 'auto') {
            return $submitted;
        }

        if ($numeric === null || $referenceMin === null && $referenceMax === null) {
            return ResultAbnormality::Normal->value;
        }

        $numeric = (float) $numeric;

        if ($referenceMin !== null && $numeric < (float) $referenceMin) {
            return ResultAbnormality::Low->value;
        }

        if ($referenceMax !== null && $numeric > (float) $referenceMax) {
            return ResultAbnormality::High->value;
        }

        return ResultAbnormality::Normal->value;
    }
}
