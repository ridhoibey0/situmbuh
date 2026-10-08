<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function provinces(Request $request)
    {
        return Province::all();
    }

    public function regencies(Request $request, $provinces_id)
    {
        return Regency::where('province_id', $provinces_id)->get();
    }

    public function districts($regency_id)
    {
        return District::where('regency_id', $regency_id)->get();
    }

    public function villages($district_id)
    {
        return Village::where('district_id', $district_id)->get();
    }

    public function searchVillages(Request $request)
    {
        return Village::with('district.regency')
            ->where('name', 'like', '%' . $request->q . '%')
            ->limit(20)
            ->get()
            ->map(function ($village) {
                return [
                    'id' => $village->id,
                    'name' => $village->name,
                    'district_name' => $village->district->name ?? '',
                    'regency_name' => $village->district->regency->name ?? '',
                ];
            });
    }
}
