<?php

namespace App\Services\Chat;

use App\Models\Child;
use App\Services\Growth\Classifier;
use App\Services\Growth\ZScoreCalculator;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Ringkasan data satu anak dalam bahasa Indonesia untuk diberikan kepada asisten AI sebagai konteks.
 *
 * Privasi: hanya nama depan, jenis kelamin, usia, ukuran, status, dan faktor prioritas yang dikirim.
 * Tidak ada NIK, nomor telepon, alamat, atau nama orang tua.
 */
class ChildContext
{
    public function __construct(private ZScoreCalculator $zScores, private Classifier $classifier)
    {
    }

    public function describe(Child $child, ?CarbonInterface $now = null): string
    {
        $now ??= now();
        $child->loadMissing('measurements', 'latestAssessment', 'kpspResults');

        $lines = [];
        $lines[] = 'Nama panggilan: ' . Str::of($child->name)->before(' ');
        $lines[] = 'Jenis kelamin: ' . ($child->gender === 'male' ? 'laki-laki' : 'perempuan');
        $lines[] = sprintf('Usia: %d bulan (lahir %s)', $child->ageInMonths($now), $child->bod->translatedFormat('d F Y'));
        $lines[] = 'Tanggal hari ini: ' . $now->translatedFormat('d F Y');

        $measurements = $child->measurements->sortBy('measured_at')->values();
        $latest = $measurements->last();

        if ($latest) {
            $at = $latest->measured_at ?? $latest->created_at;
            $age = $child->ageInMonths($at);
            $lines[] = '';
            $lines[] = sprintf('Pengukuran terakhir (%s, usia %d bulan):', $at->translatedFormat('d F Y'), $age);

            foreach ([
                ['Berat badan', 'weight', 'kg', 'BB/U'],
                ['Tinggi/panjang badan', 'height', 'cm', 'TB/U'],
                ['Lingkar kepala', 'head_circumference', 'cm', 'LK/U'],
                ['Lingkar lengan atas', 'arm_circumference', 'cm', 'LL/U'],
            ] as [$label, $field, $unit, $parameter]) {
                if ($latest->{$field} === null) {
                    continue;
                }

                $z = $this->zScores->calculate($child->gender, $parameter, $age, $latest->{$field});
                $status = $this->classifier->classify($parameter, $z);
                $lines[] = sprintf(
                    '- %s: %s %s%s',
                    $label,
                    $this->num($latest->{$field}),
                    $unit,
                    $z !== null ? sprintf(' (Z-score %s %s, status: %s)', $parameter, number_format($z, 2), $status) : ' (tidak ada acuan WHO untuk usia ini)',
                );
            }

            $history = $measurements->slice(-4, 3)->values();
            if ($history->isNotEmpty()) {
                $lines[] = 'Riwayat sebelumnya: ' . $history->map(fn($m) => sprintf(
                    '%s BB %s kg, TB %s cm',
                    ($m->measured_at ?? $m->created_at)->format('d/m/Y'),
                    $this->num($m->weight),
                    $this->num($m->height),
                ))->implode('; ');
            }
        } else {
            $lines[] = 'Belum ada data pengukuran.';
        }

        $assessment = $child->latestAssessment;
        if ($assessment) {
            $lines[] = '';
            $lines[] = 'Prioritas pemantauan (alat bantu, bukan diagnosis): ' . $assessment->level;
            foreach ($assessment->factors as $factor) {
                $lines[] = '- ' . $factor['label'] . ' (' . $factor['detail'] . ')';
            }
        }

        $followUps = $child->openFollowUps()->orderBy('due_date')->get();
        if ($followUps->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Tindak lanjut yang sedang berjalan:';
            foreach ($followUps as $f) {
                $lines[] = sprintf('- %s, tenggat %s (%s)', $f->action_type->label(), $f->due_date->translatedFormat('d F Y'), $f->status->label());
            }
        }

        $kpsp = $child->latestKpspResults()->get();
        if ($kpsp->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Hasil skrining KPSP terakhir: ' . $kpsp->pluck('interpretation')->unique()->implode('; ');
        }

        return implode("\n", $lines);
    }

    private function num($value): string
    {
        return $value === null ? '-' : rtrim(rtrim(number_format((float) $value, 2, ',', ''), '0'), ',');
    }
}
