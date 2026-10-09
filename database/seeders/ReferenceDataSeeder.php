<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Memuat data referensi KPSP (kategori usia, kategori pertanyaan, pertanyaan)
 * dari database/seeders/data/kpsp_reference.sql. Aman dijalankan ulang:
 * tabel dikosongkan lalu diisi lagi.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $sql = file_get_contents(database_path('seeders/data/kpsp_reference.sql'));

        DB::unprepared($sql);
    }
}
