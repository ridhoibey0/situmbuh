<?php

namespace App\Services\Growth;

/**
 * Klasifikasi status pertumbuhan dari Z-score per parameter (BB/U, TB/U, LK/U, LL/U).
 * Bersifat informasi pendukung, bukan diagnosis.
 */
class Classifier
{
    public const NORMAL = 'Normal';

    public function classify(string $parameter, ?float $z): ?string
    {
        if ($z === null) {
            return null;
        }

        return match ($parameter) {
            'BB/U' => match (true) {
                $z < -3 => 'Gizi Buruk',
                $z < -2 => 'Gizi Kurang',
                $z <= 1 => 'Gizi Baik',
                $z <= 2 => 'Berisiko gizi lebih',
                default => 'Gizi Lebih',
            },
            'TB/U' => match (true) {
                $z < -3 => 'Sangat Pendek',
                $z < -2 => 'Pendek',
                default => 'Normal',
            },
            'LK/U' => match (true) {
                $z < -2 => 'Mikrosefali',
                $z <= 2 => 'Normal',
                default => 'Makrosefali',
            },
            'LL/U' => match (true) {
                $z < -3 => 'Gizi Buruk',
                $z < -2 => 'Gizi Kurang',
                default => 'Gizi Normal',
            },
            default => 'Tidak Diketahui',
        };
    }
}
