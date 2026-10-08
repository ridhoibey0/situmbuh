<?php

namespace App\Http\Controllers\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'is_public' => 'nullable|boolean',
        ]);

        Testimonial::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'rating' => $request->rating,
            'is_public' => $request->boolean('is_public', true),
        ]);

        return redirect()->back()->with('success', 'Testimoni berhasil dikirim!');
    }
}
