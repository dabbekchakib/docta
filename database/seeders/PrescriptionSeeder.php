<?php

namespace Database\Seeders;

use App\Enums\PrescriptionStatus;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    public function run(): void
    {
        $consultations = Consultation::query()
            ->with('patient', 'doctor')
            ->inRandomOrder()
            ->limit(150)
            ->get();

        if ($consultations->isEmpty()) {
            return;
        }

        $creator = User::query()->first();

        foreach ($consultations as $consultation) {
            $status = $this->weightedStatus();

            $prescription = Prescription::factory()
                ->forConsultation($consultation)
                ->create([
                    'status' => $status,
                    'created_by' => $creator?->id,
                    'prescription_number' => $this->nextNumber(),
                ]);

            PrescriptionItem::factory()->count(rand(1, 4))->create([
                'prescription_id' => $prescription->id,
            ]);

            $this->reorderItems($prescription);
        }
    }

    private function weightedStatus(): PrescriptionStatus
    {
        return collect([
            PrescriptionStatus::Issued, PrescriptionStatus::Issued, PrescriptionStatus::Issued,
            PrescriptionStatus::Issued, PrescriptionStatus::Issued,
            PrescriptionStatus::Draft,
            PrescriptionStatus::Cancelled,
            PrescriptionStatus::Expired,
        ])->random();
    }

    private function nextNumber(): string
    {
        $sequence = Prescription::withTrashed()->max('id') ?? 0;

        return 'ORD-'.str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    private function reorderItems(Prescription $prescription): void
    {
        $prescription->items()
            ->orderBy('id')
            ->get()
            ->each(fn (PrescriptionItem $item, int $index) => $item->update(['sort_order' => $index + 1]));
    }
}
