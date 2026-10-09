# Situmbuh

Sistem informasi tumbuh kembang anak berbasis web yang berorientasi tindakan:
**Detect → Prioritize → Act → Follow-up → Evaluate** (proposal ICONFEST 2026, kategori Software Development).

Situmbuh bukan alat diagnosis. Skor dan label hanya membantu menentukan urutan pemantauan; keputusan klinis tetap pada tenaga kesehatan.

## Alur utama

1. **Detect**: pengukuran (BB, TB, LK, LiLA) dan hasil KPSP dihitung ke Z-score standar WHO.
2. **Prioritize + Explain**: `RiskScorer` memberi skor 0–100 dan level (rendah/sedang/tinggi) beserta daftar faktor penyebabnya.
3. **Act + Follow-up**: kader membuat tindak lanjut (jenis netral, penanggung jawab, tenggat, catatan, status).
4. **Missed monitoring**: `risk:reassess` (terjadwal harian) mendeteksi anak yang terlambat diukur dan tindak lanjut yang melewati tenggat.
5. **Evaluate**: kondisi saat tindak lanjut dibuat disimpan sebagai baseline; pengukuran pertama setelah selesai menjadi hasil, lalu dibandingkan.

## Peran

| Peran | Akses |
|---|---|
| Orang tua | Beberapa anak, pengukuran, grafik pertumbuhan, KPSP, ringkasan bahasa sederhana dan tindak lanjut yang berjalan |
| Kader | `/kader`: daftar anak berurutan prioritas, daftarkan anak, catat pengukuran, buat dan kelola tindak lanjut |
| Nakes | `/kader`: meninjau anak yang ditugaskan beserta faktor prioritas; dapat menutup tindak lanjut yang ditugaskan padanya |
| Admin | Dashboard, laporan stunting, rekap, pengelolaan pengguna dan role, konten KPSP/artikel |

## Aturan prioritas

Semua bobot dan ambang ada di [config/risk.php](config/risk.php). Ambang Z-score (-2/-3 SD) mengikuti standar WHO; bobot poin, ambang level, interval pemantauan, dan penurunan tren adalah **heuristik awal** yang perlu ditinjau bersama tenaga kesehatan sebelum dipakai di lapangan.

## Menjalankan

```bash
composer install
cp .env.example .env && php artisan key:generate   # atur DB_* dan SUMOPOD_API_KEY (untuk asisten AI, opsional)
php artisan migrate
php artisan db:seed --class=WHOGrowthStandardsSeeder
php artisan db:seed --class=DemoSeeder             # data demo fiktif (opsional)
php artisan schedule:work                          # deteksi pemantauan terlewat
```

Login demo (password `password`): kader `081100000001`, nakes `081100000002`, orang tua `081100000101`.

## Pengujian

Tes berjalan pada database terpisah yang namanya berakhiran `_test` (dijaga di `tests/TestCase.php`; `phpunit.xml` memakai `situba_test`).

```bash
mysql -uroot -e "CREATE DATABASE situba_test CHARACTER SET utf8mb4"
php vendor/bin/phpunit
```

Mencakup unit test Z-score, klasifikasi, dan skor prioritas, otorisasi per peran, serta skenario end-to-end deteksi → tindak lanjut → evaluasi.
