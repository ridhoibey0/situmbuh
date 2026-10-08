<?php

/*
|--------------------------------------------------------------------------
| Aturan prioritas pemantauan
|--------------------------------------------------------------------------
|
| Skor ini adalah ALAT BANTU PRIORITAS, bukan diagnosis. Setiap faktor
| ditampilkan beserta poinnya agar kader/nakes dapat menilai sendiri.
|
| - Ambang Z-score (-2 SD / -3 SD) mengikuti standar WHO yang sudah dipakai
|   pada klasifikasi pertumbuhan di aplikasi ini.
| - Bobot poin, ambang level, penurunan tren, dan interval pemantauan adalah
|   HEURISTIK awal yang harus ditinjau/ditetapkan bersama tenaga kesehatan
|   sebelum dipakai di lapangan.
*/

return [
    // Skor minimum untuk tiap level (dicek dari yang tertinggi).
    'levels' => [
        'tinggi' => 50,
        'sedang' => 25,
    ],

    'points' => [
        'height_severe' => 40,   // TB/U < -3 SD
        'height_moderate' => 25, // TB/U < -2 SD
        'weight_severe' => 30,   // BB/U < -3 SD
        'weight_moderate' => 15, // BB/U < -2 SD
        'zscore_drop' => 15,     // penurunan Z-score antar pengukuran
        'weight_stagnant' => 15, // berat badan tidak naik antar pengukuran
        'kpsp_referral' => 20,   // hasil KPSP menganjurkan rujukan/penyimpangan
        'kpsp_doubtful' => 10,   // hasil KPSP meragukan
        'monitoring_overdue' => 10,
        'monitoring_long_overdue' => 20, // terlambat lebih dari 2x interval
        'followup_overdue' => 15,
    ],

    // Penurunan Z-score (dalam SD) yang dianggap berarti.
    'zscore_drop_sd' => 1.0,

    // Jarak minimal antar dua pengukuran agar tren/stagnasi dinilai (hari).
    'min_gap_days' => 21,

    // Stagnasi berat badan hanya dinilai untuk anak di bawah usia ini (bulan).
    'stagnation_max_age_months' => 24,

    // Interval pemantauan yang diharapkan dan toleransi keterlambatan (hari).
    'monitoring_interval_days' => 30,
    'monitoring_grace_days' => 7,
];
