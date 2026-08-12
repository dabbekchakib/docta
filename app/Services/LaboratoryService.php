<?php

namespace App\Services;

use App\Models\Laboratory;
use App\Models\LaboratoryTest;
use Illuminate\Database\Eloquent\Collection;

class LaboratoryService
{
    /**
     * Laboratoires actifs (triés par nom) pour les sélecteurs.
     *
     * @return Collection<int, Laboratory>
     */
    public function activeLaboratories(): Collection
    {
        return Laboratory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Examens actifs (triés par nom) pour les sélecteurs.
     *
     * @return Collection<int, LaboratoryTest>
     */
    public function activeTests(): Collection
    {
        return LaboratoryTest::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, int>
     */
    public function activeTestIds(): array
    {
        return $this->activeTests()->pluck('id')->all();
    }
}
