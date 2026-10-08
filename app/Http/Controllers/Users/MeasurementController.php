<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\UserMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeasurementController extends Controller
{
    public function index(Request $request)
    {
        $child = $request->attributes->get('child');
        $this->authorize('recordMeasurement', $child);

        return view('pages.users.measurement.create', ['child' => $child]);
    }

    public function store(Request $request)
    {
        $child = $request->attributes->get('child');
        $this->authorize('recordMeasurement', $child);

        $data = $request->validate([
            'weight' => ['required', 'numeric', 'between:0.5,30'],
            'height' => ['required', 'numeric', 'between:20,130'],
            'head_circumference' => ['required', 'numeric', 'between:20,60'],
            'arm_circumference' => ['required', 'numeric', 'between:5,30'],
            'measured_at' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . $child->bod->toDateString()],
        ]);

        UserMeasurement::create($data + [
            'child_id' => $child->id,
            'measured_by' => Auth::id(),
        ]);

        return redirect()->route('growth.index')->with('success', 'Data has been saved successfully!');
    }
}
