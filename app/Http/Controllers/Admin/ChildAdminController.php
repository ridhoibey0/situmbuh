<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Pengelolaan anak oleh admin: melihat seluruh anak, menugaskan kader/tenaga kesehatan,
 * dan menautkan akun orang tua.
 */
class ChildAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $level = $request->input('level');

        $children = Child::query()
            ->with('latestAssessment', 'latestMeasurement', 'parent:id,parent_name,phone', 'staff:id,name')
            ->when($search !== '', fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->when(in_array($level, ['tinggi', 'sedang', 'rendah'], true), fn($q) => $q->whereHas('latestAssessment', fn($a) => $a->where('level', $level)))
            ->when($request->boolean('unassigned'), fn($q) => $q->doesntHave('staff'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.children.index', [
            'children' => $children,
            'search' => $search,
            'level' => $level,
            'unassigned' => $request->boolean('unassigned'),
        ]);
    }

    public function show(Child $child)
    {
        $child->load('latestAssessment', 'parent', 'staff', 'registeredBy:id,name');

        $assignable = User::query()
            ->whereIn('roles', [UserRole::Kader->value, UserRole::Nakes->value])
            ->whereNotIn('id', $child->staff->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'name', 'roles']);

        return view('pages.admin.children.show', compact('child', 'assignable'));
    }

    public function assignStaff(Request $request, Child $child)
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->whereIn('roles', [UserRole::Kader->value, UserRole::Nakes->value])],
        ]);

        $user = User::findOrFail($data['user_id']);
        $child->staff()->syncWithoutDetaching([$user->id => ['role' => $user->roles->value]]);

        return back()->with('success', "{$user->name} ditugaskan pada {$child->name}.");
    }

    public function removeStaff(Child $child, User $user)
    {
        $child->staff()->detach($user->id);

        return back()->with('success', "Penugasan {$user->name} dicabut.");
    }

    public function linkParent(Request $request, Child $child)
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:20']]);

        $parent = User::where('roles', UserRole::Parent->value)
            ->get(['id', 'phone', 'parent_name'])
            ->first(fn($u) => Phone::digits($u->phone) === Phone::digits($data['phone']));

        if (!$parent) {
            return back()->withErrors(['phone' => 'Tidak ada akun orang tua dengan nomor tersebut.'])->withInput();
        }

        $child->update(['parent_id' => $parent->id, 'parent_phone' => null]);

        return back()->with('success', "{$child->name} tertaut ke akun {$parent->parent_name}.");
    }

    public function unlinkParent(Child $child)
    {
        $child->update(['parent_id' => null]);

        return back()->with('success', 'Tautan orang tua dilepas.');
    }
}
