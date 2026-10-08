<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CheckProfileCompletion;
use App\Models\Child;
use App\Models\UserMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChildController extends Controller
{
    public function index(Request $request)
    {
        $children = Auth::user()->children()->with('latestMeasurement')->orderBy('id')->get();

        return view('pages.users.children.index', [
            'children' => $children,
            'activeId' => $request->session()->get(CheckProfileCompletion::SESSION_KEY) ?? $children->first()?->id,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Child::class);

        return view('pages.users.children.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Child::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'bod' => ['required', 'date', 'before_or_equal:today'],
            'weight' => ['required', 'numeric', 'between:0.5,30'],
            'height' => ['required', 'numeric', 'between:20,130'],
            'head_circumference' => ['required', 'numeric', 'between:20,60'],
            'arm_circumference' => ['required', 'numeric', 'between:5,30'],
        ]);

        $user = Auth::user();

        $child = DB::transaction(function () use ($data, $user) {
            $child = Child::create([
                'parent_id' => $user->id,
                'registered_by' => $user->id,
                'name' => $data['name'],
                'gender' => $data['gender'],
                'bod' => $data['bod'],
                'village_id' => $user->village_id,
            ]);

            // Data kelahiran menjadi pengukuran pertama.
            UserMeasurement::create([
                'child_id' => $child->id,
                'measured_by' => $user->id,
                'weight' => $data['weight'],
                'height' => $data['height'],
                'head_circumference' => $data['head_circumference'],
                'arm_circumference' => $data['arm_circumference'],
                'measured_at' => $data['bod'],
            ]);

            return $child;
        });

        $request->session()->put(CheckProfileCompletion::SESSION_KEY, $child->id);

        return redirect()->route('children.index')->with('success', "Data {$child->name} berhasil ditambahkan.");
    }

    public function select(Request $request, Child $child)
    {
        $this->authorize('view', $child);

        $request->session()->put(CheckProfileCompletion::SESSION_KEY, $child->id);

        return redirect()->route('growth.index');
    }
}
