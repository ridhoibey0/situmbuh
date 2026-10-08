<?php

namespace App\Services\FollowUp;

use App\Enums\FollowUpStatus;
use App\Models\Child;
use App\Models\RiskAssessment;

/**
 * Menghubungkan tindak lanjut yang sudah selesai dengan hasil pemantauan berikutnya:
 * pengukuran pertama pada/sesudah tanggal selesai menjadi "kondisi sesudah" (outcome).
 */
class OutcomeLinker
{
    public function link(Child $child): void
    {
        $pending = $child->followUps()
            ->where('status', FollowUpStatus::Done->value)
            ->whereNull('outcome_assessment_id')
            ->get();

        foreach ($pending as $followUp) {
            $measurement = $child->measurements()
                ->whereDate('measured_at', '>=', $followUp->completed_at->toDateString())
                ->orderBy('measured_at')->orderBy('id')
                ->first();

            if (!$measurement) {
                continue;
            }

            $assessment = RiskAssessment::where('child_id', $child->id)
                ->where('measurement_id', $measurement->id)
                ->latest('id')
                ->first();

            if ($assessment) {
                $followUp->update(['outcome_assessment_id' => $assessment->id]);
            }
        }
    }
}
