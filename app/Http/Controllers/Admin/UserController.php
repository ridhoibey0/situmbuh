<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $query = User::query();

            return Datatables::of($query)
                ->addColumn('action', function ($item) {
                    return '
            <div class="btn-group">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle"
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
                        route('users.edit', $item->id) .
                        '">
                                Sunting
                            </a>
                        </li>
                        <li>
                            <form action="' .
                        route('users.destroy', $item->id) .
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
                ->editColumn('roles', fn($item) => $item->roles?->label() ?? '-')
                ->editColumn('created_at', fn($item) => $item->created_at?->translatedFormat('d M Y'))
                ->rawColumns(['action'])
                ->make();
        }

        return view('pages.admin.user.index');
    }

    public function edit($id)
    {
        $item = User::findOrFail($id);

        return view('pages.admin.user.edit', [
            'item' => $item,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate(['roles' => ['nullable', Rule::enum(UserRole::class)]]);

        $data = $request->except('password');

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Berhasil diperbarui');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Berhasil menghapus user');
    }
}
