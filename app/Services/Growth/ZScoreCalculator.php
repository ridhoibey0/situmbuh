<?php

namespace App\Services\Growth;

use App\Models\WhoGrowthStandard;

/**
 * Menghitung Z-score dari tabel standar WHO (interpolasi linear per SD).
 *
 * Tabel referensi dimuat sekali per instance sehingga tidak ada query per pengukuran.
 */
class ZScoreCalculator
{
    /** @var array<string, array{sd_median: float, sd_1_positif: float, sd_1_negatif: float}>|null */
    private ?array $standards;

    /**
     * @param  array<string, array>|null  $standards  Diindeks "gender|parameter|umur_bulan". Null = muat dari DB.
     */
    public function __construct(?array $standards = null)
    {
        $this->standards = $standards;
    }

    public function calculate(?string $gender, string $parameter, int $ageInMonths, $actualValue): ?float
    {
        if ($actualValue === null || $actualValue === '' || $gender === null) {
            return null;
        }

        $ref = $this->standards()["{$gender}|{$parameter}|{$ageInMonths}"] ?? null;

        if (!$ref) {
            return null;
        }

        $median = (float) $ref['sd_median'];
        $actualValue = (float) $actualValue;
        $sdPositif = $ref['sd_1_positif'] - $median;
        $sdNegatif = $median - $ref['sd_1_negatif'];

        if ($actualValue == $median) {
            return 0.0;
        }

        $sd = $actualValue > $median ? $sdPositif : $sdNegatif;

        if ($sd == 0.0) {
            return null;
        }

        return round(($actualValue - $median) / $sd, 2);
    }

    private function standards(): array
    {
        if ($this->standards === null) {
            $this->standards = WhoGrowthStandard::query()
                ->get(['gender', 'parameter', 'age_in_months', 'sd_median', 'sd_1_positif', 'sd_1_negatif'])
                ->mapWithKeys(fn($row) => ["{$row->gender}|{$row->parameter}|{$row->age_in_months}" => $row->only(['sd_median', 'sd_1_positif', 'sd_1_negatif'])])
                ->all();
        }

        return $this->standards;
    }
}
