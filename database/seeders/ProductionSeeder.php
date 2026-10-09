<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data minimum agar aplikasi dapat dipakai pada instalasi baru:
 * tabel standar WHO, pertanyaan KPSP, data wilayah (opsional), dan akun
 * admin awal bila ADMIN_PHONE dan ADMIN_PASSWORD diisi.
 *
 *   php artisan db:seed --class=ProductionSeeder
 *
 * Set SEED_REGIONS=false untuk melewati data wilayah (±80 ribu desa).
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(WHOGrowthStandardsSeeder::class);
        $this->call(ReferenceDataSeeder::class);

        // seeder wilayah memakai insert biasa, jadi lewati bila data sudah ada
        if (filter_var(env('SEED_REGIONS', true), FILTER_VALIDATE_BOOLEAN) && !DB::table('provinces')->exists()) {
            $this->call(IndoRegionSeeder::class);
        }

        $this->call(AdminSeeder::class);
    }
}
