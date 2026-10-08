<?php

namespace Tests\Unit\Risk;

use App\Models\Child;
use App\Models\KpspResult;
use App\Models\UserMeasurement;
use App\Services\Growth\ZScoreCalculator;
use App\Services\Risk\RiskScorer;
use Carbon\Carbon;
use Tests\TestCase;

class RiskScorerTest extends TestCase
{
    private Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = Carbon::parse('2026-10-07');
    }

    private function config(): array
    {
        return [
            'levels' => ['tinggi' => 50, 'sedang' => 25],
            'points' => [
                'height_severe' => 40, 'height_moderate' => 25, 'weight_severe' => 30, 'weight_moderate' => 15,
                'zscore_drop' => 15, 'weight_stagnant' => 15, 'kpsp_referral' => 20, 'kpsp_doubtful' => 10,
                'monitoring_overdue' => 10, 'monitoring_long_overdue' => 20, 'followup_overdue' => 15,
            ],
            'zscore_drop_sd' => 1.0,
            'min_gap_days' => 21,
            'stagnation_max_age_months' => 24,
            'monitoring_interval_days' => 30,
            'monitoring_grace_days' => 7,
        ];
    }

    /** Tabel WHO mini untuk anak laki-laki usia 6 dan 8 bulan. */
    private function scorer(): RiskScorer
    {
        $standards = [];
        foreach ([6 => [67.6, 2.5, 7.9, 0.9], 8 => [70.6, 2.6, 8.6, 1.0]] as $age => [$tb, $tbSd, $bb, $bbSd]) {
            $standards["male|TB/U|{$age}"] = ['sd_median' => $tb, 'sd_1_positif' => $tb + $tbSd, 'sd_1_negatif' => $tb - $tbSd];
            $standards["male|BB/U|{$age}"] = ['sd_median' => $bb, 'sd_1_positif' => $bb + $bbSd, 'sd_1_negatif' => $bb - $bbSd];
        }

        return new RiskScorer(new ZScoreCalculator($standards), $this->config());
    }

    private function child(array $measurements, array $kpsp = []): Child
    {
        $child = new Child(['name' => 'Tes', 'gender' => 'male', 'bod' => $this->now->copy()->subMonths(8)->subDays(10)->toDateString()]);
        $child->setRelation('measurements', collect(array_map(fn($m) => new UserMeasurement([
            'weight' => $m[1], 'height' => $m[2], 'measured_at' => Carbon::parse($m[0]),
        ]), $measurements)));
        $child->setRelation('latestKpspResults', collect(array_map(fn($k) => new KpspResult($k), $kpsp)));

        return $child;
    }

    private function codes(array $result): array
    {
        return array_column($result['factors'], 'code');
    }

    public function test_healthy_recent_child_has_low_priority_and_no_factors(): void
    {
        // BB 8.6 / TB 70.6 pada 8 bulan = median, diukur 3 hari lalu.
        $result = $this->scorer()->assess($this->child([['2026-10-04', 8.6, 70.6]]), $this->now);

        $this->assertSame(0, $result['score']);
        $this->assertSame('rendah', $result['level']);
        $this->assertSame([], $result['factors']);
        $this->assertSame(0.0, $result['metrics']['z_tb']);
    }

    public function test_severely_short_child_is_high_priority_with_explanation(): void
    {
        // TB 62.0 pada 8 bln: (62-70.6)/2.6 = -3.31 SD
        $result = $this->scorer()->assess($this->child([['2026-10-04', 8.6, 62.0]]), $this->now);

        $this->assertSame(['height_severe'], $this->codes($result));
        $this->assertSame(40, $result['score']);
        $this->assertSame('sedang', $result['level']);
        $this->assertStringContainsString('-3.31', $result['factors'][0]['detail']);
    }

    public function test_multiple_factors_add_up_and_reach_high_level(): void
    {
        // Usia 6 bln saat diukur: TB 62.1 (-2.2 SD) 25 + BB 5.5 (-2.67 SD) 15 + terlewat 61 hari 20 = 60
        $result = $this->scorer()->assess($this->child([['2026-08-07', 5.5, 62.1]]), $this->now);

        $this->assertEqualsCanonicalizing(['height_moderate', 'weight_moderate', 'monitoring_long_overdue'], $this->codes($result));
        $this->assertSame(60, $result['score']);
        $this->assertSame('tinggi', $result['level']);
    }

    public function test_weight_stagnation_and_zscore_drop_detected_between_measurements(): void
    {
        // 6 bln: BB 7.9 (median, z 0). 8 bln: BB 7.9 -> tidak naik, z = (7.9-8.6)/1.0 = -0.7 (turun 0.7 < 1, bukan drop)
        $result = $this->scorer()->assess($this->child([
            ['2026-08-07', 7.9, 67.6],
            ['2026-10-05', 7.9, 70.6],
        ]), $this->now);

        $this->assertSame(['weight_stagnant'], $this->codes($result));
    }

    public function test_zscore_drop_factor(): void
    {
        // BB turun dari median (z 0 @ 6 bln) ke 6.4 kg @ 8 bln: z = (6.4-8.6)/1.0 = -2.2 -> drop 2.2 SD, juga BB kurang
        $result = $this->scorer()->assess($this->child([
            ['2026-08-07', 7.9, 67.6],
            ['2026-10-05', 6.4, 70.6],
        ]), $this->now);

        $this->assertEqualsCanonicalizing(['weight_moderate', 'zscore_drop', 'weight_stagnant'], $this->codes($result));
    }

    public function test_trend_ignored_when_measurements_too_close(): void
    {
        $result = $this->scorer()->assess($this->child([
            ['2026-10-01', 7.9, 67.6],
            ['2026-10-05', 7.9, 70.6],
        ]), $this->now);

        $this->assertSame([], $this->codes($result));
    }

    public function test_never_measured_child_is_flagged(): void
    {
        $result = $this->scorer()->assess($this->child([]), $this->now);

        $this->assertSame(['monitoring_overdue'], $this->codes($result));
        $this->assertSame('Belum pernah diukur', $result['factors'][0]['label']);
    }

    public function test_overdue_boundaries(): void
    {
        // 37 hari = batas (30 + 7 toleransi) -> aman; 38 hari -> terlambat; 61 hari -> terlambat panjang
        $ok = $this->scorer()->assess($this->child([['2026-08-31', 8.6, 70.6]]), $this->now);
        $late = $this->scorer()->assess($this->child([['2026-08-30', 8.6, 70.6]]), $this->now);
        $long = $this->scorer()->assess($this->child([['2026-08-07', 8.6, 70.6]]), $this->now);

        $this->assertSame([], $this->codes($ok));
        $this->assertSame(['monitoring_overdue'], $this->codes($late));
        $this->assertSame(['monitoring_long_overdue'], $this->codes($long));
    }

    public function test_kpsp_referral_outranks_doubtful_and_only_one_factor_is_added(): void
    {
        $result = $this->scorer()->assess($this->child([['2026-10-04', 8.6, 70.6]], [
            ['interpretation' => 'Meragukan', 'intervensi' => 'Lakukan pemeriksaan lanjutan'],
            ['interpretation' => 'Ada kemungkinan penyimpangan', 'intervensi' => 'Rujuk ke RS rujukan tumbuh kembang level 1.'],
            ['interpretation' => 'Sesuai umur', 'intervensi' => 'Selamat!'],
        ]), $this->now);

        $this->assertSame(['kpsp_referral'], $this->codes($result));
        $this->assertSame(20, $result['score']);
    }

    public function test_score_is_capped_at_100(): void
    {
        $config = $this->config();
        $config['points']['height_severe'] = 120;
        $scorer = new RiskScorer(new ZScoreCalculator([
            'male|TB/U|8' => ['sd_median' => 70.6, 'sd_1_positif' => 73.2, 'sd_1_negatif' => 68.0],
        ]), $config);

        $result = $scorer->assess($this->child([['2026-10-04', 5.0, 55.0]]), $this->now);

        $this->assertSame(100, $result['score']);
        $this->assertSame('tinggi', $result['level']);
    }

    public function test_level_thresholds(): void
    {
        $scorer = $this->scorer();

        $this->assertSame('rendah', $scorer->levelFor(24));
        $this->assertSame('sedang', $scorer->levelFor(25));
        $this->assertSame('sedang', $scorer->levelFor(49));
        $this->assertSame('tinggi', $scorer->levelFor(50));
    }
}
