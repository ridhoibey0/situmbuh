<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Services\Growth\Classifier;
use App\Services\Growth\ZScoreCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StuntingController extends Controller
{
    public function __construct(private ZScoreCalculator $zScores, private Classifier $classifier)
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $villageFilter = $request->input('village_id');
        $severityFilter = $request->input('severity'); // 'sangat_pendek' | 'pendek' | '' (all)

        $children = $this->buildStuntingList();

        $children = collect($children)
            ->when($search !== '', function ($items) use ($search) {
                $needle = strtolower($search);
                return $items->filter(function ($child) use ($needle) {
                    return str_contains(strtolower($child['name']), $needle)
                        || str_contains(strtolower($child['village']), $needle);
                });
            })
            ->when($villageFilter, function ($items) use ($villageFilter) {
                return $items->where('village_id', (int) $villageFilter);
            })
            ->when($severityFilter, function ($items) use ($severityFilter) {
                $target = $severityFilter === 'sangat_pendek' ? 'Sangat Pendek' : 'Pendek';
                return $items->where('status', $target);
            })
            ->sortBy('z_tb')
            ->values();

        $perPage = 15;
        $page = max(1, (int) $request->input('page', 1));
        $total = $children->count();
        $paged = $children->forPage($page, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paged,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $villages = DB::table('villages')
            ->whereIn('id', collect($children)->pluck('village_id')->unique()->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        $summary = [
            'total' => count($children),
            'sangat_pendek' => collect($children)->where('status', 'Sangat Pendek')->count(),
            'pendek' => collect($children)->where('status', 'Pendek')->count(),
        ];

        return view('pages.admin.stunting.index', [
            'children' => $paginator,
            'villages' => $villages,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'village_id' => $villageFilter,
                'severity' => $severityFilter,
            ],
        ]);
    }

    public function show(Child $child)
    {
        $child->load('measurements', 'parent');

        $village = DB::table('villages')->where('id', $child->village_id)->value('name') ?? '-';
        $district = DB::table('districts')
            ->where('id', DB::table('villages')->where('id', $child->village_id)->value('district_id'))
            ->value('name') ?? '-';

        $history = $child->measurements
            ->sortBy('measured_at')
            ->map(function ($m) use ($child) {
                $measuredAt = $m->measured_at ?? $m->created_at;
                $ageInMonths = $child->ageInMonths(Carbon::parse($measuredAt));

                $zTB = $this->zScores->calculate($child->gender, 'TB/U', $ageInMonths, $m->height);
                $zBB = $this->zScores->calculate($child->gender, 'BB/U', $ageInMonths, $m->weight);

                $status = $this->classifier->classify('TB/U', $zTB) ?? 'Normal';

                return [
                    'measured_at' => Carbon::parse($measuredAt),
                    'age_in_months' => $ageInMonths,
                    'weight' => $m->weight,
                    'height' => $m->height,
                    'head_circumference' => $m->head_circumference,
                    'arm_circumference' => $m->arm_circumference,
                    'z_tb' => $zTB,
                    'z_bb' => $zBB,
                    'status' => $status,
                ];
            })
            ->values();

        $latest = $history->last();

        return view('pages.admin.stunting.detail', [
            'child' => $child,
            'village' => $village,
            'district' => $district,
            'history' => $history,
            'latest' => $latest,
        ]);
    }

    private function buildStuntingList()
    {
        $users = Child::query()
            ->select('id', 'parent_id', 'name', 'gender', 'bod', 'village_id')
            ->with([
                'parent:id,phone',
                'latestMeasurement' => function ($query) {
                    $query->select(
                        'user_measurements.id',
                        'user_measurements.child_id',
                        'user_measurements.weight',
                        'user_measurements.height',
                        'user_measurements.measured_at',
                        'user_measurements.created_at'
                    );
                },
            ])
            ->get();

        $villageNames = DB::table('villages')->pluck('name', 'id');
        $result = [];

        foreach ($users as $child) {
            $measurement = $child->latestMeasurement;
            if (!$measurement || !$measurement->height || !$child->bod) {
                continue;
            }

            $measuredAt = $measurement->measured_at ?? $measurement->created_at;
            $ageInMonths = $child->ageInMonths(Carbon::parse($measuredAt));
            $zTB = $this->zScores->calculate($child->gender, 'TB/U', $ageInMonths, $measurement->height);

            if ($zTB === null || $zTB >= -2) {
                continue;
            }

            $status = $this->classifier->classify('TB/U', $zTB);

            $result[] = [
                'id' => $child->id,
                'name' => $child->name,
                'gender' => $child->gender,
                'phone' => $child->parent?->phone,
                'village_id' => $child->village_id,
                'village' => $villageNames[$child->village_id] ?? '-',
                'age_in_months' => $ageInMonths,
                'height' => $measurement->height,
                'weight' => $measurement->weight,
                'z_tb' => $zTB,
                'status' => $status,
                'measured_at' => Carbon::parse($measuredAt),
            ];
        }

        return $result;
    }
}
