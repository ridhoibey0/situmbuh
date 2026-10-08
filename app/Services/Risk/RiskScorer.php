<?php

namespace App\Services\Risk;

use App\Models\Child;
use App\Models\UserMeasurement;
use App\Services\Growth\ZScoreCalculator;
use Carbon\CarbonInterface;

/**
 * Menghitung skor prioritas pemantauan anak berdasarkan aturan di config/risk.php.
 *
 * Hasil berupa skor, level, dan daftar faktor yang menjelaskan asal skor (explainable).
 * Bukan diagnosis.
 *
 * Child harus sudah memuat relasi: measurements, latestKpspResults, dan (opsional) openFollowUps.
 */
class RiskScorer
{
    public const LEVEL_LOW = 'rendah';
    public const LEVEL_MEDIUM = 'sedang';
    public const LEVEL_HIGH = 'tinggi';

    private array $config;

    public function __construct(private ZScoreCalculator $zScores, ?array $config = null)
    {
        $this->config = $config ?? config('risk');
    }

    /**
     * @return array{score: int, level: string, factors: list<array>, metrics: array}
     */
    public function assess(Child $child, ?CarbonInterface $now = null): array
    {
        $now ??= now();
        $measurements = $child->measurements->sortBy('measured_at')->values();
        $latest = $measurements->last();
        $previous = $measurements->count() > 1 ? $measurements[$measurements->count() - 2] : null;

        $factors = [];
        $metrics = ['z_tb' => null, 'z_bb' => null];

        if ($latest) {
            $metrics['z_tb'] = $this->zFor($child, $latest, 'TB/U', 'height');
            $metrics['z_bb'] = $this->zFor($child, $latest, 'BB/U', 'weight');
        }

        array_push(
            $factors,
            ...$this->heightFactors($metrics['z_tb']),
            ...$this->weightFactors($metrics['z_bb']),
            ...$this->trendFactors($child, $previous, $latest, $metrics),
            ...$this->kpspFactors($child),
            ...$this->monitoringFactors($latest, $now),
            ...$this->followUpFactors($child, $now),
        );

        $score = min(100, array_sum(array_column($factors, 'points')));

        return [
            'score' => $score,
            'level' => $this->levelFor($score),
            'factors' => $factors,
            'metrics' => $metrics,
        ];
    }

    public function levelFor(int $score): string
    {
        return match (true) {
            $score >= $this->config['levels']['tinggi'] => self::LEVEL_HIGH,
            $score >= $this->config['levels']['sedang'] => self::LEVEL_MEDIUM,
            default => self::LEVEL_LOW,
        };
    }

    private function zFor(Child $child, UserMeasurement $m, string $parameter, string $field): ?float
    {
        $value = $m->{$field};
        if ($value === null) {
            return null;
        }

        return $this->zScores->calculate($child->gender, $parameter, $child->ageInMonths($m->measured_at ?? $m->created_at), $value);
    }

    private function heightFactors(?float $z): array
    {
        $p = $this->config['points'];

        if ($z === null || $z >= -2) {
            return [];
        }

        $severe = $z < -3;

        return [$this->factor(
            $severe ? 'height_severe' : 'height_moderate',
            $severe ? 'Tinggi badan sangat pendek untuk usianya' : 'Tinggi badan pendek untuk usianya',
            sprintf('TB/U %.2f SD (batas -%d SD)', $z, $severe ? 3 : 2),
            $severe ? $p['height_severe'] : $p['height_moderate'],
        )];
    }

    private function weightFactors(?float $z): array
    {
        $p = $this->config['points'];

        if ($z === null || $z >= -2) {
            return [];
        }

        $severe = $z < -3;

        return [$this->factor(
            $severe ? 'weight_severe' : 'weight_moderate',
            $severe ? 'Berat badan sangat kurang untuk usianya' : 'Berat badan kurang untuk usianya',
            sprintf('BB/U %.2f SD (batas -%d SD)', $z, $severe ? 3 : 2),
            $severe ? $p['weight_severe'] : $p['weight_moderate'],
        )];
    }

    private function trendFactors(Child $child, ?UserMeasurement $previous, ?UserMeasurement $latest, array $metrics): array
    {
        if (!$previous || !$latest || !$this->farEnough($previous, $latest)) {
            return [];
        }

        $p = $this->config['points'];
        $factors = [];

        $drops = [];
        foreach ([['TB/U', 'height', $metrics['z_tb']], ['BB/U', 'weight', $metrics['z_bb']]] as [$param, $field, $zNow]) {
            $zBefore = $this->zFor($child, $previous, $param, $field);
            if ($zBefore !== null && $zNow !== null && ($zBefore - $zNow) >= $this->config['zscore_drop_sd']) {
                $drops[] = sprintf('%s turun %.2f SD (%.2f → %.2f)', $param, $zBefore - $zNow, $zBefore, $zNow);
            }
        }

        if ($drops) {
            $factors[] = $this->factor('zscore_drop', 'Z-score turun dibanding pengukuran sebelumnya', implode('; ', $drops), $p['zscore_drop']);
        }

        $ageAtLatest = $child->ageInMonths($latest->measured_at ?? $latest->created_at);
        if (
            $ageAtLatest < $this->config['stagnation_max_age_months']
            && $previous->weight !== null && $latest->weight !== null
            && (float) $latest->weight <= (float) $previous->weight
        ) {
            $factors[] = $this->factor(
                'weight_stagnant',
                'Berat badan tidak naik',
                sprintf('%.2f kg → %.2f kg sejak %s', $previous->weight, $latest->weight, ($previous->measured_at ?? $previous->created_at)->format('d/m/Y')),
                $p['weight_stagnant'],
            );
        }

        return $factors;
    }

    private function kpspFactors(Child $child): array
    {
        $p = $this->config['points'];
        $worst = null;

        foreach ($child->latestKpspResults as $result) {
            $points = match (true) {
                stripos((string) $result->intervensi, 'rujuk') !== false => $p['kpsp_referral'],
                stripos((string) $result->interpretation, 'meragukan') !== false => $p['kpsp_doubtful'],
                default => 0,
            };

            if ($points > 0 && (!$worst || $points > $worst['points'])) {
                $worst = ['points' => $points, 'interpretation' => $result->interpretation];
            }
        }

        if (!$worst) {
            return [];
        }

        return [$this->factor(
            $worst['points'] === $p['kpsp_referral'] ? 'kpsp_referral' : 'kpsp_doubtful',
            'Hasil skrining perkembangan (KPSP) perlu perhatian',
            $worst['interpretation'],
            $worst['points'],
        )];
    }

    private function monitoringFactors(?UserMeasurement $latest, CarbonInterface $now): array
    {
        $p = $this->config['points'];
        $interval = $this->config['monitoring_interval_days'];
        $allowed = $interval + $this->config['monitoring_grace_days'];

        if (!$latest) {
            return [$this->factor('monitoring_overdue', 'Belum pernah diukur', 'Belum ada data pengukuran', $p['monitoring_overdue'])];
        }

        $days = (int) ($latest->measured_at ?? $latest->created_at)->diffInDays($now);

        if ($days <= $allowed) {
            return [];
        }

        $long = $days > $interval * 2;

        return [$this->factor(
            $long ? 'monitoring_long_overdue' : 'monitoring_overdue',
            'Pemantauan terlewat',
            sprintf('Terakhir diukur %d hari lalu (jadwal setiap %d hari)', $days, $interval),
            $long ? $p['monitoring_long_overdue'] : $p['monitoring_overdue'],
        )];
    }

    /** Tindak lanjut yang melewati tenggat; relasi opsional (aktif sejak fase follow-up). */
    private function followUpFactors(Child $child, CarbonInterface $now): array
    {
        if (!$child->relationLoaded('openFollowUps')) {
            return [];
        }

        $overdue = $child->openFollowUps->filter(fn($f) => $f->due_date && $f->due_date->lt($now->copy()->startOfDay()));

        if ($overdue->isEmpty()) {
            return [];
        }

        return [$this->factor(
            'followup_overdue',
            'Tindak lanjut melewati tenggat',
            $overdue->count() . ' tindak lanjut belum selesai setelah tenggat',
            $this->config['points']['followup_overdue'],
        )];
    }

    private function farEnough(UserMeasurement $a, UserMeasurement $b): bool
    {
        return abs(($a->measured_at ?? $a->created_at)->diffInDays($b->measured_at ?? $b->created_at)) >= $this->config['min_gap_days'];
    }

    private function factor(string $code, string $label, string $detail, int $points): array
    {
        return compact('code', 'label', 'detail', 'points');
    }
}
