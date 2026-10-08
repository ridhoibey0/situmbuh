<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgeCategory;
use Yajra\DataTables\Facades\DataTables;

class AgeCategoryController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $query = AgeCategory::query();

            return Datatables::of($query)
                ->addColumn('action', function ($item) {
                    return '
            <div class="btn-group">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle"
                        type="button" id="dropdownMenuButton' .
                        $item->id .
                        '"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Aksi
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton' .
                        $item->id .
                        '">
                        <li>
                            <a class="dropdown-item" href="' .
                        route('age-category.edit', $item->id) .
                        '">
                                Sunting
                            </a>
                        </li>
                        <li>
                            <form action="' .
                        route('age-category.destroy', $item->id) .
                        '" method="POST" style="display: inline;">
                                ' .
                        method_field('delete') .
                        csrf_field() .
                        '
                                <button type="submit" class="dropdown-item text-danger">
                                    Hapus
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>';
                })
                ->rawColumns(['action'])
                ->make();
        }

        return view('pages.admin.age-category.index');
    }

    public function create()
    {
        return view('pages.admin.age-category.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'min_age' => 'required',
            'max_age' => 'required',
        ]);

        $data = $request->all();
        AgeCategory::create($data);
        return redirect()->route('age-category.index');
    }

    public function edit($id)
    {
        $item = AgeCategory::findOrFail($id);

        return view('pages.admin.age-category.edit', [
            'item' => $item,
        ]);
    }

    public function update(Request $request, $id) {
        $data = $request->all();

        $item = AgeCategory::findOrFail($id);
        $item->update($data);

        return redirect()->route('age-category.index');
    }

    public function destroy($id)
    {
        $item = AgeCategory::findOrFail($id);
        $item->delete();
        return redirect()->route('age-category.index');
    }
}
