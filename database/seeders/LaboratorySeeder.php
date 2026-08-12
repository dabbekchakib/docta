<?php

namespace Database\Seeders;

use App\Enums\ConsultationStatus;
use App\Models\Consultation;
use App\Models\Laboratory;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryTest;
use App\Models\ReferenceRange;
use App\Models\Sample;
use App\Models\TestCategory;
use App\Services\LaboratoryReportService;
use App\Services\LaboratoryRequestService;
use App\Services\LaboratoryResultService;
use Illuminate\Database\Seeder;

class LaboratorySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCatalog();

        $consultations = Consultation::query()
            ->whereIn('status', [ConsultationStatus::Completed->value, ConsultationStatus::InProgress->value])
            ->inRandomOrder()
            ->limit(30)
            ->get();

        if ($consultations->isEmpty()) {
            $this->command?->warn('Aucune consultation disponible : les demandes d\'examen ne seront pas créées.');
        }

        $requestService = app(LaboratoryRequestService::class);
        $resultService = app(LaboratoryResultService::class);
        $reportService = app(LaboratoryReportService::class);

        $tests = LaboratoryTest::query()->where('is_active', true)->get();

        $i = 0;

        foreach ($consultations as $consultation) {
            $stage = $i % 5;
            $i++;

            $count = $consultation->laboratoryRequests()->count();

            if ($count >= 2) {
                continue;
            }

            $selectedTests = $tests->random(min(3, $tests->count()));

            $request = $requestService->create(
                [
                    'patient_id' => $consultation->patient_id,
                    'doctor_id' => $consultation->doctor_id,
                    'consultation_id' => $consultation->id,
                    'laboratory_id' => Laboratory::query()->first()?->id,
                    'requested_at' => $consultation->consultation_date?->toDateString() ?? now()->toDateString(),
                    'priority' => 'normal',
                    'clinical_information' => 'Bilan demandé lors de la consultation.',
                ],
                $selectedTests
                    ->map(fn (LaboratoryTest $test, int $index): array => [
                        'laboratory_test_id' => $test->id,
                        'sample_type' => $test->sample_type->value,
                        'sort_order' => $index + 1,
                    ])
                    ->values()
                    ->all()
            );

            if ($stage === 0) {
                continue;
            }

            $request = $requestService->submit($request);

            if ($stage === 1) {
                continue;
            }

            $request = $requestService->accept($request);

            if ($stage === 2) {
                continue;
            }

            foreach ($request->items as $item) {
                $requestService->collectSample($request, $item->id, null);
            }

            $requestService->receiveSamples($request);
            $requestService->processSamples($request);

            if ($stage === 3) {
                continue;
            }

            $rows = $request->items
                ->map(function ($item): array {
                    $range = $item->test?->referenceRanges()->where('gender', 'all')->first();

                    if ($range) {
                        $value = (string) $range->min_value;
                        $numeric = (float) $range->min_value;
                    } else {
                        $value = 'Négatif';
                        $numeric = null;
                    }

                    return [
                        'laboratory_request_item_id' => $item->id,
                        'parameter_name' => $item->test?->name,
                        'value' => $value,
                        'numeric_value' => $numeric,
                        'unit' => $range?->unit ?? $item->test?->unit,
                        'reference_min' => $range?->min_value,
                        'reference_max' => $range?->max_value,
                        'reference_text' => $item->test?->default_reference_value,
                        'abnormality' => 'normal',
                        'comment' => null,
                    ];
                })
                ->values()
                ->all();

            $resultService->syncResults($request, $rows);
            $request = $resultService->validate($request->fresh());
            $reportService->generate($request);
        }
    }

    /**
     * Crée le catalogue de base (laboratoires, catégories, examens, intervalles).
     */
    private function seedCatalog(): void
    {
        if (Laboratory::query()->doesntExist()) {
            Laboratory::factory()->count(2)->create(['is_active' => true]);
        }

        $categories = [
            ['name' => 'Hématologie', 'code' => 'HEMA', 'description' => 'Numération et formule sanguine.'],
            ['name' => 'Biochimie', 'code' => 'BIOCH', 'description' => 'Analyses biochimiques du sang.'],
            ['name' => 'Sérologie', 'code' => 'SERO', 'description' => 'Détection d\'anticorps.'],
            ['name' => 'Bactériologie', 'code' => 'BACT', 'description' => 'Examens bactériologiques.'],
            ['name' => 'Hormonologie', 'code' => 'HORMO', 'description' => 'Dosages hormonaux.'],
        ];

        $categoryMap = [];

        foreach ($categories as $categoryData) {
            $category = TestCategory::query()
                ->firstOrCreate(['code' => $categoryData['code']], $categoryData);

            $categoryMap[$category->code] = $category;
        }

        $tests = [
            ['code' => 'NFS', 'name' => 'Numération formule sanguine', 'category' => 'HEMA', 'sample' => 'blood', 'unit' => '10³/µL', 'reference' => '4,5 – 11'],
            ['code' => 'GLY', 'name' => 'Glycémie à jeun', 'category' => 'BIOCH', 'sample' => 'blood', 'unit' => 'mmol/L', 'reference' => '0,7 – 1,1'],
            ['code' => 'UREE', 'name' => 'Urée sanguine', 'category' => 'BIOCH', 'sample' => 'blood', 'unit' => 'mmol/L', 'reference' => '2,5 – 7,5'],
            ['code' => 'CREAT', 'name' => 'Créatinine', 'category' => 'BIOCH', 'sample' => 'blood', 'unit' => 'µmol/L', 'reference' => '60 – 110'],
            ['code' => 'TSH', 'name' => 'TSH ultrasensible', 'category' => 'HORMO', 'sample' => 'blood', 'unit' => 'mUI/L', 'reference' => '0,4 – 4,0'],
            ['code' => 'CRP', 'name' => 'Protéine C réactive', 'category' => 'BIOCH', 'sample' => 'blood', 'unit' => 'mg/L', 'reference' => '< 5'],
            ['code' => 'CRO', 'name' => 'Examen cytobactériologique des urines', 'category' => 'BACT', 'sample' => 'urine', 'unit' => null, 'reference' => null],
        ];

        foreach ($tests as $testData) {
            $category = $categoryMap[$testData['category']];

            $test = LaboratoryTest::query()
                ->firstOrCreate(
                    ['code' => $testData['code']],
                    [
                        'test_category_id' => $category->id,
                        'name' => $testData['name'],
                        'description' => null,
                        'sample_type' => $testData['sample'],
                        'unit' => $testData['unit'],
                        'default_reference_value' => $testData['reference'],
                        'is_active' => true,
                        'requires_fasting' => $testData['code'] === 'GLY',
                        'instructions' => $testData['code'] === 'GLY' ? 'Prélèvement à jeun (12 h).' : null,
                    ]
                );

            if ($test->referenceRanges()->doesntExist() && $testData['reference'] !== null) {
                ReferenceRange::factory()->create([
                    'laboratory_test_id' => $test->id,
                    'gender' => 'all',
                    'min_value' => 1,
                    'max_value' => 100,
                    'unit' => $testData['unit'],
                ]);
            }
        }
    }
}
