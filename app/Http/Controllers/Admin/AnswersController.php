<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KpspAnswer;
use Yajra\DataTables\Facades\DataTables;

class AnswersController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $query = KpspAnswer::with(['question.category', 'result.child']);

            return Datatables::of($query)
                ->addColumn('status', function ($row) {
                    // Cek nilai kolom 'answer'
                    if ($row->answer) {
                        return '<span class="badge bg-success">Yes</span>';
                    }
                    return '<span class="badge bg-danger">No</span>';
                })
                ->rawColumns(['status']) // Untuk memastikan kolom 'status' di-render sebagai HTML
                ->make();
        }
        return view('pages.admin.answers.index');
    }
}
