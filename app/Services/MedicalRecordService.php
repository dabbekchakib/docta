<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MedicalRecordService
{
    /**
     * Génère un numéro de dossier médical unique (DMP-000001).
     */
    public function generateMedicalRecordNumber(): string
    {
        $sequence = MedicalRecord::withTrashed()->max('id') ?? 0;

        return 'DMP-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Récupère le dossier médical du patient, en le créant si nécessaire.
     * Processus idempotent : un seul dossier par patient.
     */
    public function ensureForPatient(Patient $patient): MedicalRecord
    {
        // Utilise le query builder pour éviter de mettre en cache une relation
        // « null » sur le modèle si le dossier est créé juste après la lecture.
        $record = $patient->medicalRecord()->first();

        if ($record) {
            return $record;
        }

        $record = $patient->medicalRecord()->create([
            'medical_record_number' => $this->generateMedicalRecordNumber(),
            'blood_group' => $patient->blood_group?->value,
        ]);

        return $record;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): MedicalRecord
    {
        $patient = Patient::findOrFail($data['patient_id']);

        return $this->update($this->ensureForPatient($patient), $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(MedicalRecord $record, array $data): MedicalRecord
    {
        $record->fill($data);
        $record->save();

        return $record;
    }

    /**
     * Résumé médical affiché en haut de la fiche DMP.
     *
     * @return array<string, mixed>
     */
    public function summary(MedicalRecord $record): array
    {
        $patient = $record->patient;

        return [
            'patient' => $patient->full_name,
            'patient_number' => $patient->patient_number,
            'age' => $patient->age,
            'gender' => $patient->gender?->getLabel(),
            'blood_group' => $record->fullBloodGroup ?? $patient->blood_group?->getLabel(),
            'critical_allergies' => $record->criticalAllergies(),
            'chronic_diseases' => $record->activeChronicDiseases(),
            'medications' => $record->activeMedications(),
            'last_consultation' => $patient->consultations()->latest('consultation_date')->first(),
            'next_appointment' => $patient->appointments()
                ->whereIn('status', ['confirmed', 'waiting'])
                ->whereDate('appointment_date', '>=', now()->toDateString())
                ->orderBy('appointment_date')
                ->orderBy('start_time')
                ->first(),
        ];
    }

    /**
     * Timeline médicale chronologique (descendante).
     * Extensible pour Prescription, LaboratoryResult, Imaging, Certificate...
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function timeline(MedicalRecord $record): Collection
    {
        $patient = $record->patient;

        $events = collect();

        foreach ($patient->consultations()->with('doctor')->get() as $consultation) {
            $events->push($this->event(
                $consultation->consultation_date?->startOfDay(),
                'consultation',
                'Consultation',
                'Dr '.($consultation->doctor?->full_name ?? '—'),
                $consultation->reason,
                'info'
            ));
        }

        foreach ($record->medicalHistories()->get() as $item) {
            $events->push($this->event(
                $item->diagnosed_at?->startOfDay(),
                'antecedent',
                'Antécédent',
                $item->title,
                $item->description,
                'gray'
            ));
        }

        foreach ($record->surgicalHistories()->get() as $item) {
            $events->push($this->event(
                $item->performed_at?->startOfDay(),
                'intervention',
                'Intervention chirurgicale',
                $item->procedure_name,
                $item->hospital,
                'purple'
            ));
        }

        foreach ($record->allergies()->get() as $item) {
            $events->push($this->event(
                $item->discovered_at?->startOfDay(),
                'allergie',
                'Allergie',
                $item->allergen,
                $item->severity->getLabel().($item->reaction ? ' — '.$item->reaction : ''),
                $item->isCritical() ? 'danger' : 'warning'
            ));
        }

        foreach ($record->chronicDiseases()->get() as $item) {
            $events->push($this->event(
                $item->diagnosed_at?->startOfDay(),
                'maladie_chronique',
                'Maladie chronique',
                $item->disease_name,
                $item->icd_code ? 'CIM-10 : '.$item->icd_code : null,
                'danger'
            ));
        }

        foreach ($record->vaccinations()->get() as $item) {
            $events->push($this->event(
                $item->administered_at?->startOfDay(),
                'vaccination',
                'Vaccination',
                $item->vaccine_name,
                $item->dose_number ? 'Dose '.$item->dose_number : null,
                'success'
            ));
        }

        foreach ($record->medicalDocuments()->get() as $item) {
            $events->push($this->event(
                $item->document_date?->startOfDay(),
                'document',
                'Document médical',
                $item->title,
                $item->document_type?->getLabel(),
                'primary'
            ));
        }

        foreach ($patient->laboratoryRequests()->with('items.test')->get() as $request) {
            $tests = $request->items->pluck('test.name')->filter()->take(3)->implode(', ');

            $events->push($this->event(
                $request->requested_at?->startOfDay(),
                'examen_laboratoire',
                'Examen de laboratoire',
                $request->request_number,
                collect([
                    $request->status->getLabel(),
                    $tests !== '' ? $tests : null,
                ])->filter()->implode(' — '),
                $request->isValidated()
                    ? 'success'
                    : ($request->status === \App\Enums\LaboratoryRequestStatus::Cancelled ? 'danger' : 'warning')
            ));
        }

        return $events
            ->filter(fn (array $event): bool => $event['date'] !== null)
            ->sortByDesc(fn (array $event): string => $event['date']->toDateString())
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function event(?Carbon $date, string $type, string $typeLabel, string $title, ?string $description, string $color): array
    {
        return [
            'date' => $date,
            'type' => $type,
            'typeLabel' => $typeLabel,
            'title' => $title,
            'description' => $description,
            'color' => $color,
        ];
    }
}
