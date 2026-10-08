<?php

namespace App\Http\Controllers\Users;
use App\Http\Controllers\Controller;
use App\Services\Growth\Classifier;
use App\Services\Growth\ZScoreCalculator;
use App\Models\Testimonial;
use App\Models\WhoGrowthStandard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class GrowthController extends Controller
{
    public function __construct(private ZScoreCalculator $zScores, private Classifier $classifier)
    {
    }

    public function index(Request $request)
    {
        $userId = Auth::user()->id;
        $child = $request->attributes->get('child');
        $this->authorize('view', $child);

        $weightMeasurements = DB::table('user_measurements')->where('user_measurements.child_id', $child->id)->join('children', 'children.id', '=', 'user_measurements.child_id')->select('user_measurements.weight', 'user_measurements.height', 'user_measurements.head_circumference', 'user_measurements.arm_circumference', 'user_measurements.measured_at', 'children.bod')->orderBy('measured_at')->get();
        $groupedByMonth = [];
        $maxUsiaBulan = 0;

        foreach ($weightMeasurements as $m) {
            $birthDate = \Carbon\Carbon::parse($m->bod);
            $measuredDate = \Carbon\Carbon::parse($m->measured_at);
            $diff = $birthDate->diff($measuredDate);
            $usiaBulan = $diff->y * 12 + $diff->m;

            // Simpan usia bulan tertinggi
            $maxUsiaBulan = max($maxUsiaBulan, $usiaBulan);

            $key = $usiaBulan;

            $z = $this->zScores->calculate($child->gender, 'BB/U', $usiaBulan, $m->weight);
            $zTB = $this->zScores->calculate($child->gender, 'TB/U', $usiaBulan, $m->height);
            $zLK = $this->zScores->calculate($child->gender, 'LK/U', $usiaBulan, $m->head_circumference);
            $zLL = $this->zScores->calculate($child->gender, 'LL/U', $usiaBulan, $m->arm_circumference);

            $klasifikasi = $z !== null ? $this->classifier->classify('BB/U', $z) : null;
            $klasifikasiTB = $zTB !== null ? $this->classifier->classify('TB/U', $zTB) : null;
            $klasifikasiLK = $zLK !== null ? $this->classifier->classify('LK/U', $zLK) : null;
            $klasifikasiLL = $zLL !== null ? $this->classifier->classify('LL/U', $zLL) : null;

            if (!isset($groupedByMonth[$key]) || \Carbon\Carbon::parse($m->measured_at)->greaterThan(\Carbon\Carbon::parse($groupedByMonth[$key]['measured_at']))) {
                $groupedByMonth[$key] = [
                    'usia' => "{$diff->y}T {$diff->m}B {$diff->d}H",
                    'usia_bulan' => $usiaBulan,
                    'berat' => (float) $m->weight,
                    'tinggi' => (float) $m->height,
                    'kepala' => (float) $m->head_circumference,
                    'lengan' => (float) $m->arm_circumference,
                    'measured_at' => $m->measured_at,
                    'zBB' => $z,
                    'zTB' => $zTB,
                    'zLK' => $zLK,
                    'zLL' => $zLL,
                    'klasifikasiBB' => $klasifikasi,
                    'klasifikasiTB' => $klasifikasiTB,
                    'klasifikasiLK' => $klasifikasiLK,
                    'klasifikasiLL' => $klasifikasiLL,
                ];
            }
        }

        // Ambil tanggal pengukuran terakhir untuk tiap bulan
        $tooltips = [];
        foreach (range(0, $maxUsiaBulan) as $bulan) {
            $last = collect($weightMeasurements)
                ->filter(function ($m) use ($bulan) {
                    $birth = \Carbon\Carbon::parse($m->bod);
                    $measured = \Carbon\Carbon::parse($m->measured_at);
                    return $birth->diffInMonths($measured) === $bulan;
                })
                ->sortByDesc('measured_at')
                ->first();

            if ($last) {
                $tooltips[$bulan] = $last->measured_at;
            }
        }

        // Finalisasi data: isi semua bulan, isi data kosong jika tidak ada pengukuran
        $weightData = [];
        for ($i = 0; $i <= $maxUsiaBulan; $i++) {
            if (isset($groupedByMonth[$i])) {
                $data = $groupedByMonth[$i];
            } else {
                $data = [
                    'usia' => floor($i / 12) . 'T ' . $i % 12 . 'B',
                    'usia_bulan' => $i,
                    'berat' => null,
                    'measured_at' => null,
                    'z' => null,
                ];
            }

            $data['hasTooltip'] = $data['measured_at'] == ($tooltips[$i] ?? null);
            $weightData[] = $data;
        }

        $reference = $this->referenceBands($child->gender, $maxUsiaBulan);

        $sudahTestimoni = Testimonial::where('user_id', $userId)->exists();

        $tampilkanModalTestimoni = false;

        if ($weightMeasurements->count() >= 2 && !$sudahTestimoni) {
            $tampilkanModalTestimoni = true;
        }

        return view('pages.users.growth-monitoring.index', [
            'weightData' => $weightData,
            'reference' => $reference,
            'usia' => "{$child->bod->diff(now())->y}T {$child->bod->diff(now())->m}B {$child->bod->diff(now())->d}H",
            'child' => $child,
            'assessment' => $child->assessments()->latest('computed_at')->latest('id')->first(),
            'followUps' => $child->openFollowUps()->orderBy('due_date')->get(),
            'tampilkanModalTestimoni' => $tampilkanModalTestimoni
        ]);
    }

    /**
     * Pita referensi WHO (median dan batas ±2 serta ±3 SD) per bulan untuk tiap parameter, agar grafik
     * memperlihatkan posisi anak terhadap rentang normal.
     *
     * @return array<string, array{median: list<?float>, low: list<?float>, high: list<?float>, low3: list<?float>, high3: list<?float>}>
     */
    private function referenceBands(?string $gender, int $maxAge): array
    {
        $out = [];

        foreach (['weight' => 'BB/U', 'height' => 'TB/U', 'head' => 'LK/U', 'arm' => 'LL/U'] as $key => $parameter) {
            $rows = WhoGrowthStandard::where('gender', $gender)->where('parameter', $parameter)
                ->whereBetween('age_in_months', [0, $maxAge])->get()->keyBy('age_in_months');

            $band = ['median' => [], 'low' => [], 'high' => [], 'low3' => [], 'high3' => []];
            for ($age = 0; $age <= $maxAge; $age++) {
                $r = $rows->get($age);
                $band['median'][] = $r ? (float) $r->sd_median : null;
                $band['low'][] = $r ? (float) $r->sd_2_negatif : null;
                $band['high'][] = $r ? (float) $r->sd_2_positif : null;
                $band['low3'][] = $r ? (float) $r->sd_3_negatif : null;
                $band['high3'][] = $r ? (float) $r->sd_3_positif : null;
            }
            $out[$key] = $band;
        }

        return $out;
    }
}
