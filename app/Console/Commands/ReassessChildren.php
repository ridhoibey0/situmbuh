<?php

namespace App\Console\Commands;

use App\Models\Child;
use App\Services\Risk\AssessChild;
use Illuminate\Console\Command;

class ReassessChildren extends Command
{
    protected $signature = 'risk:reassess {--force : Simpan penilaian baru walau hasilnya sama}';

    protected $description = 'Hitung ulang prioritas semua anak (mendeteksi pemantauan terlewat dan tenggat tindak lanjut).';

    public function handle(AssessChild $assess): int
    {
        $count = 0;

        Child::query()->chunkById(100, function ($children) use ($assess, &$count) {
            foreach ($children as $child) {
                $assess->handle($child, (bool) $this->option('force'));
                $count++;
            }
        });

        $this->info("{$count} anak dinilai ulang.");

        return self::SUCCESS;
    }
}
