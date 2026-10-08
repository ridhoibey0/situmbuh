<?php

namespace App\Observers;

use App\Models\KpspResult;
use App\Models\UserMeasurement;
use App\Services\Risk\AssessChild;

/**
 * Menghitung ulang prioritas setiap kali ada pengukuran atau hasil KPSP baru.
 */
class AssessmentTriggerObserver
{
    public function __construct(private AssessChild $assess)
    {
    }

    public function created(UserMeasurement|KpspResult $model): void
    {
        $child = $model->child;

        if ($child) {
            $this->assess->handle($child);
        }
    }

    public function deleted(UserMeasurement|KpspResult $model): void
    {
        $this->created($model);
    }
}
