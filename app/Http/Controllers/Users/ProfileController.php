<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Profil akun orang tua. Data anak dikelola di ChildController.
 */
class ProfileController extends Controller
{
    public function index()
    {
        $provinces = Province::all();
        $user = Auth::user();

        return view('pages.users.profile.index', compact('provinces', 'user'));
    }

    public function update(Request $request, $id)
    {
        abort_unless((int) $id === Auth::id(), 403);

        $request->validate([
            'name' => 'required',
            'parent_name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'parent_name', 'province_id', 'regency_id', 'district_id', 'village_id', 'address_detail']);

        if ($request->file('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('assets/avatar', 'public');
        }

        Auth::user()->update($data);

        return redirect()->route('profile.index')->with('success', 'Data has been saved successfully!');
    }
}
