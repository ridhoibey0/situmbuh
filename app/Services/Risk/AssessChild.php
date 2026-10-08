<?php

namespace App\Services\Risk;

use App\Models\Child;
use App\Models\RiskAssessment;
use App\Services\FollowUp\OutcomeLinker;

/**
 * Menghitung dan menyimpan penilaian prioritas seorang anak.
 * Riwayat dipertahankan, tetapi baris baru hanya dibuat bila hasilnya berubah.
 */
class AssessChild
{
    public function __construct(private RiskScorer $scorer, private OutcomeLinker $outcomes)
    {
    }

    public function handle(Child $child, bool $force = false): RiskAssessment
    {
        $child->load(['measurements', 'latestKpspResults', 'openFollowUps']);

        $result = $this->scorer->assess($child);
        $latestMeasurementId = $child->measurements->sortBy('measured_at')->last()?->id;

        $current = $child->assessments()->latest('computed_at')->latest('id')->first();

        if (!$force && $current && $this->sameResult($current, $result, $latestMeasurementId)) {
            $assessment = $current;
        } else {
            $assessment = $child->assessments()->create([
                'measurement_id' => $latestMeasurementId,
                'score' => $result['score'],
                'level' => $result['level'],
                'factors' => $result['factors'],
                'z_tb' => $result['metrics']['z_tb'],
                'z_bb' => $result['metrics']['z_bb'],
                'computed_at' => now(),
            ]);
        }

        // Pengukuran baru dapat menjadi "kondisi sesudah" bagi tindak lanjut yang sudah selesai.
        $this->outcomes->link($child);

        return $assessment;
    }

    private function sameResult(RiskAssessment $current, array $result, ?int $measurementId): bool
    {
        return $current->measurement_id === $measurementId
            && $current->score === $result['score']
            && $current->factors == $result['factors'];
    }
}
