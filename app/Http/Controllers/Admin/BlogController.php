<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BlogController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $query = Blog::with('author'); // Eager load relasi

            return Datatables::of($query)
                ->addColumn('action', function ($item) {
                    return '
                    <div class="btn-group">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle"
                                type="button" id="dropdownMenuButton' . $item->id . '"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Aksi
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $item->id . '">
                                <li>
                                    <a class="dropdown-item" href="' . route('blogs.edit', $item->id) . '">
                                        Sunting
                                    </a>
                                </li>
                                <li>
                                    <form action="' . route('blogs.destroy', $item->id) . '" method="POST" style="display: inline;">
                                        ' . method_field('delete') . csrf_field() . '
                                        <button type="submit" class="dropdown-item text-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>';
                })
                ->addColumn('published_status', function ($item) {
                    return $item->is_published ? 'Published' : 'Draft';
                })
                ->rawColumns(['action'])
                ->make();
        }

        return view('pages.admin.blogs.index');
    }

    public function create()
    {
        return view('pages.admin.blogs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        $data = $request->all();

        if ($request->file('image')) {
            $data['image'] = $request->file('image')->store('assets/blogs', 'public');
        }
        
        $data['slug'] = Str::slug($request->title);

        // Tambahkan author ID
        $data['author_id'] = auth()->id();

        Blog::create($data);
        return redirect()->route('blogs.index');
    }

    public function edit($id)
    {
        $item = Blog::findOrFail($id);

        return view('pages.admin.blogs.edit', [
            'item' => $item,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $item = Blog::findOrFail($id);

        // Handle file upload and delete old image
        if ($request->file('image')) {
            if ($item->image && \Storage::disk('public')->exists($item->image)) {
                \Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('assets/blogs', 'public');
        }

        $item->update($data);
        return redirect()->route('blogs.index');
    }

    public function destroy($id)
    {
        $item = Blog::findOrFail($id);

        // Delete associated image
        if ($item->image && \Storage::disk('public')->exists($item->image)) {
            \Storage::disk('public')->delete($item->image);
        }

        $item->delete();
        return redirect()->route('blogs.index');
    }
}
