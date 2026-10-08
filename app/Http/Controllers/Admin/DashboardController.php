<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RekapExport;
use App\Http\Controllers\Controller;
use App\Models\KpspResult;
use App\Models\Province;
use App\Services\Admin\DashboardMetrics;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(DashboardMetrics $metrics)
    {
        return view('pages.admin.dashboard', [
            'm' => $metrics->summary(6),
            'kpspThisMonth' => KpspResult::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
        ]);
    }

    public function rekap()
    {
        $provinces = Province::all();
        return view('pages.admin.rekapan.index', compact('provinces'));
    }

    public function export(Request $request)
    {
        $periode = $request->input('periode');
        [$startDate, $endDate] = explode(' - ', $periode);
        $villageId = $request->input('village_id');
        return Excel::download(new RekapExport($startDate, $endDate, $villageId), 'rekap.xlsx');
    }
}
