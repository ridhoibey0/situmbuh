<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KpspQuestion;
use App\Models\AgeCategory;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class KpspQuestionController extends Controller
{
    public function index()
    {
        $category = QuestionCategory::all();
        $ageCategory = AgeCategory::all();
        if (request()->ajax()) {
            $query = KpspQuestion::with('ageCategory', 'category');
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
                        route('questions.edit', $item->id) .
                        '">
                                Sunting
                            </a>
                        </li>
                        <li>
                            <form action="' .
                        route('questions.destroy', $item->id) .
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
                ->editColumn('question', fn($item) => \Illuminate\Support\Str::limit(trim(strip_tags($item->question)), 220))
                ->editColumn('image', function ($item) {
                    return $item->image ? '<img src="' . Storage::url($item->image) . '" style="max-height: 80px;"/>' : '';
                })
                ->filter(function($query) {
                    if(request()->filled("category")) {
                         $query->whereHas('category', function ($q) {
                            $q->where('id', request()->category);
                        });
                    }

                    if(request()->filled("ageCategory")) {
                         $query->whereHas('ageCategory', function ($q) {
                            $q->where('id', request()->ageCategory);
                        });
                    }
                })
                ->rawColumns(['action', 'image'])
                ->make();
        }

        return view('pages.admin.questions.index', compact('category', 'ageCategory'));
    }

    public function create()
    {
        $category = AgeCategory::all();
        $questionCategory = QuestionCategory::all();
        return view('pages.admin.questions.create', compact('category', 'questionCategory'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required',
            'age_category_id' => 'required',
            'category_id' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $data = $request->all();
        if ($request->file('image')) {
            $data['image'] = $request->file('image')->store('assets/product', 'public');
        }

        KpspQuestion::create($data);
        return redirect()->route('questions.index');
    }

    public function edit($id)
    {
        $item = KpspQuestion::findOrFail($id);
        $category = AgeCategory::all();
        $questionCategory = QuestionCategory::all();
        return view('pages.admin.questions.edit', compact('category', 'item', 'questionCategory'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        $item = KpspQuestion::findOrFail($id);
        if ($request->file('image')) {
            if ($item->image && \Storage::disk('public')->exists($item->image)) {
                \Storage::disk('public')->delete($item->image);
            }

            $data['image'] = $request->file('image')->store('assets/product', 'public');
        }

        $item->update($data);

        return redirect()->route('questions.index');
    }

    public function show($id) {}

    public function destroy($id)
    {
        $item = KpspQuestion::findOrFail($id);
        if ($item->image && \Storage::disk('public')->exists($item->image)) {
            \Storage::disk('public')->delete($item->image);
        }
        $item->delete();
        return redirect()->route('questions.index');
    }
}
