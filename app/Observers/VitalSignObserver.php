<?php

namespace App\Observers;

use App\Models\VitalSign;

class VitalSignObserver
{
    public function saving(VitalSign $vitalSign): void
    {
        $bmi = VitalSign::computeBmi(
            (float) $vitalSign->weight,
            (float) $vitalSign->height,
        );

        if ($bmi !== null) {
            $vitalSign->bmi = $bmi;
        }
    }
}
