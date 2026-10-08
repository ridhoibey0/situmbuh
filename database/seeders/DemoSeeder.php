<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Child;
use App\Models\KpspResult;
use App\Models\User;
use App\Models\WhoGrowthStandard;
use App\Services\FollowUp\FollowUpService;
use App\Services\Risk\AssessChild;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data demo fiktif untuk mendemonstrasikan alur Detect → Prioritize → Act → Follow-up → Evaluate.
 * Semua nama dan nomor telepon fiktif; tidak memakai data anak sebenarnya.
 *
 * Jalankan: php artisan db:seed --class=DemoSeeder
 * Prasyarat: tabel who_growth_standards sudah terisi (WHOGrowthStandardsSeeder).
 *
 * Login demo (password: "password"): kader 081100000001, nakes 081100000002, orang tua 081100000101.
 */
class DemoSeeder extends Seeder
{
    private const KADER_PHONE = '081100000001';

    /** Indeks anak => indeks akun orang tua. Setiap orang tua memegang satu anak sehat dan satu anak dengan skenario berbeda. */
    private const PARENT_OF = [0 => 0, 8 => 0, 1 => 1, 12 => 1, 2 => 2, 17 => 2, 3 => 3, 25 => 3];

    private Carbon $today;

    public function run(): void
    {
        if (!WhoGrowthStandard::exists()) {
            $this->command?->error('Tabel who_growth_standards kosong. Jalankan WHOGrowthStandardsSeeder terlebih dahulu.');

            return;
        }

        if (User::where('phone', self::KADER_PHONE)->exists()) {
            $this->command?->warn('Data demo sudah ada, seeder dilewati.');

            return;
        }

        $this->today = Carbon::now()->startOfDay();

        $kader = $this->user('Kader Demo', self::KADER_PHONE, UserRole::Kader);
        $nakes = $this->user('Bidan Demo', '081100000002', UserRole::Nakes);
        $parents = collect(range(1, 4))->map(fn($i) => $this->user("Orang Tua Demo {$i}", sprintf('0811000001%02d', $i), UserRole::Parent));

        foreach ($this->specs() as $i => [$name, $gender, $ageMonths, $scenario]) {
            $child = Child::create([
                'parent_id' => isset(self::PARENT_OF[$i]) ? $parents->get(self::PARENT_OF[$i])?->id : null,
                'registered_by' => $kader->id,
                'name' => $name,
                'gender' => $gender,
                'bod' => $this->today->copy()->subMonths($ageMonths)->subDays(3)->toDateString(),
            ]);
            $child->staff()->attach($kader->id, ['role' => 'kader']);

            if (in_array($scenario, ['severe', 'kpsp', 'declining'], true)) {
                $child->staff()->attach($nakes->id, ['role' => 'nakes']);
            }

            $this->play($child, $scenario, $kader);
        }

        // Hitung ulang dengan waktu nyata agar pemantauan terlewat dan tenggat terdeteksi.
        Carbon::setTestNow();
        $assess = app(AssessChild::class);
        Child::query()->each(fn(Child $child) => $assess->handle($child));

        $this->command?->info(Child::count() . ' anak demo dibuat. Login kader: ' . self::KADER_PHONE . ' / password');
    }

    /** @return list<array{string, string, int, string}> nama, gender, usia (bulan), skenario */
    private function specs(): array
    {
        return [
            ['Aisyah Putri', 'female', 14, 'healthy'], ['Bagas Pratama', 'male', 10, 'healthy'],
            ['Citra Lestari', 'female', 18, 'healthy'], ['Dimas Saputra', 'male', 8, 'healthy'],
            ['Eka Wulandari', 'female', 22, 'healthy'], ['Fajar Nugroho', 'male', 12, 'healthy'],
            ['Gita Maharani', 'female', 16, 'healthy'], ['Hasan Firdaus', 'male', 20, 'healthy'],
            ['Indah Permata', 'female', 15, 'stunted'], ['Joko Santoso', 'male', 24, 'stunted'],
            ['Kirana Dewi', 'female', 19, 'stunted'], ['Lutfi Hakim', 'male', 13, 'stunted'],
            ['Mawar Sari', 'female', 17, 'severe'], ['Naufal Ramadhan', 'male', 21, 'severe'],
            ['Oka Wijaya', 'male', 9, 'stagnant'], ['Putri Ayu', 'female', 11, 'stagnant'],
            ['Qori Amalia', 'female', 14, 'stagnant'], ['Raka Aditya', 'male', 10, 'declining'],
            ['Sinta Nuraini', 'female', 12, 'declining'], ['Tegar Wicaksono', 'male', 16, 'missed'],
            ['Umi Kalsum', 'female', 18, 'missed'], ['Vino Alamsyah', 'male', 14, 'missed'],
            ['Wulan Safitri', 'female', 20, 'missed'], ['Xavier Pranata', 'male', 15, 'kpsp'],
            ['Yasmin Azzahra', 'female', 13, 'kpsp'], ['Zaki Mubarok', 'male', 11, 'improved'],
            ['Alya Rahma', 'female', 14, 'improved'], ['Bima Satria', 'male', 17, 'overdue_followup'],
            ['Cahya Ningrum', 'female', 12, 'overdue_followup'], ['Damar Jati', 'male', 3, 'never'],
        ];
    }

    /** Memainkan riwayat anak dari masa lalu ke sekarang agar skor dan riwayat konsisten dengan waktunya. */
    private function play(Child $child, string $scenario, User $kader): void
    {
        $followUps = app(FollowUpService::class);

        $series = match ($scenario) {
            'healthy' => [[90, 0.2, 0.1], [60, 0.0, 0.3], [30, 0.1, -0.1], [6, 0.0, 0.2]],
            'stunted' => [[90, -2.3, -1.0], [60, -2.4, -1.1], [30, -2.5, -1.0], [8, -2.4, -0.8]],
            'severe' => [[90, -3.2, -3.0], [60, -3.4, -3.1], [30, -3.3, -3.2], [5, -3.4, -3.1]],
            'stagnant' => [[90, -0.4, -0.6], [60, -0.5, -0.8], [30, -0.4, -0.9], [7, -0.4, -1.0]],
            'declining' => [[60, 0.0, 0.0], [30, -0.3, -1.1], [6, -0.6, -2.4]],
            'missed' => [[150, 0.0, 0.2], [120, 0.1, 0.0], [75, 0.0, 0.1]],
            'kpsp' => [[60, 0.0, 0.1], [30, 0.1, 0.0], [4, 0.0, 0.1]],
            'improved' => [[75, -2.8, -2.6]],
            'overdue_followup' => [[40, -2.6, -1.4], [6, -2.5, -1.3]],
            default => [],
        };

        $stagnantWeight = null;
        foreach ($series as [$daysAgo, $zTB, $zBB]) {
            $at = $this->travel($daysAgo);

            if ($at->lt($child->bod)) {
                continue;
            }

            $age = $child->ageInMonths($at);
            $weight = $this->valueFor($child->gender, 'BB/U', $age, $zBB);

            if ($scenario === 'stagnant') {
                $weight = $stagnantWeight ??= $weight; // berat tidak naik antar pengukuran
            }

            $child->measurements()->create([
                'weight' => $weight,
                'height' => $this->valueFor($child->gender, 'TB/U', $age, $zTB),
                'head_circumference' => $this->valueFor($child->gender, 'LK/U', $age, 0.0),
                'arm_circumference' => $this->valueFor($child->gender, 'LL/U', $age, 0.0),
                'measured_at' => $at,
                'measured_by' => $kader->id,
            ]);
        }

        if ($scenario === 'kpsp') {
            $this->travel(4);
            KpspResult::create([
                'child_id' => $child->id, 'age_category_id' => 1, 'yes_count' => 5,
                'interpretation' => 'Ada kemungkinan penyimpangan',
                'intervensi' => 'Rujuk ke RS rujukan tumbuh kembang level 1.',
            ]);
        }

        if ($scenario === 'improved') {
            $this->travel(73);
            $followUp = $followUps->create($child, $kader, [
                'action_type' => 'nutrition_counseling', 'due_date' => $this->today->copy()->subDays(55)->toDateString(),
                'notes' => 'Konseling gizi dan pola asuh bersama orang tua',
            ]);

            $this->travel(60);
            $followUps->changeStatus($followUp, $kader, \App\Enums\FollowUpStatus::Done, null, 'Konseling selesai, orang tua paham jadwal makan.');

            foreach ([[40, -1.7, -1.1], [6, -1.2, -0.6]] as [$daysAgo, $zTB, $zBB]) {
                $at = $this->travel($daysAgo);
                $age = $child->ageInMonths($at);
                $child->measurements()->create([
                    'weight' => $this->valueFor($child->gender, 'BB/U', $age, $zBB),
                    'height' => $this->valueFor($child->gender, 'TB/U', $age, $zTB),
                    'head_circumference' => $this->valueFor($child->gender, 'LK/U', $age, 0.0),
                    'arm_circumference' => $this->valueFor($child->gender, 'LL/U', $age, 0.0),
                    'measured_at' => $at, 'measured_by' => $kader->id,
                ]);
            }
        }

        if ($scenario === 'overdue_followup') {
            $this->travel(35);
            $followUps->create($child, $kader, [
                'action_type' => 'home_visit', 'due_date' => $this->today->copy()->subDays(14)->toDateString(),
                'notes' => 'Kunjungan rumah untuk memantau asupan dan pola makan',
            ]);
        }

        if ($scenario === 'never') {
            $this->travel(1);
        }
    }

    private function travel(int $daysAgo): Carbon
    {
        $at = $this->today->copy()->subDays($daysAgo)->setTime(9, 0);
        Carbon::setTestNow($at);

        return $at;
    }

    /** Nilai pengukuran yang menghasilkan Z-score tertentu menurut tabel WHO yang dipakai aplikasi. */
    private function valueFor(string $gender, string $parameter, int $age, float $z): ?float
    {
        $ref = WhoGrowthStandard::where(compact('gender', 'parameter'))->where('age_in_months', $age)->first();

        if (!$ref) {
            return null;
        }

        $sd = $z < 0 ? $ref->sd_median - $ref->sd_1_negatif : $ref->sd_1_positif - $ref->sd_median;

        return round($ref->sd_median + $z * $sd, 1);
    }

    private function user(string $name, string $phone, UserRole $role): User
    {
        return User::create([
            'name' => $name,
            'parent_name' => $name,
            'phone' => $phone,
            'bod' => '1990-01-01',
            'password' => Hash::make('password'),
            'roles' => $role,
        ]);
    }
}
