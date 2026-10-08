<?php

namespace App\Exports;

use App\Models\Child;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class RekapExport implements FromView
{
    protected $startDate;
    protected $endDate;
    protected $villageId;
    /**
     * @return \Illuminate\Support\Collection
     */

    public function __construct($startDate, $endDate, $villageId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->villageId = $villageId;
    }
    public function view(): View
    {
        $query = Child::with('latestKpspResults.categoryQuestion', 'latestMeasurement', 'parent')
        ->whereBetween("created_at", [$this->startDate, $this->endDate]);
        if(!empty($this->villageId)){
            $query->where('village_id', $this->villageId);
        }

        $data = $query->get();
        return view('exports.rekap', compact('data'));
    }
}
