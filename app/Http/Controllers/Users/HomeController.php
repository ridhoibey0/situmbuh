<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Support\ActiveChild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $child = ActiveChild::resolve($request, Auth::user());

        if ($child) {
            View::share('activeChild', $child);
            $child->load('latestMeasurement', 'latestAssessment');
        }

        return view('pages.users.home', [
            'child' => $child,
            'assessment' => $child?->latestAssessment,
            'followUps' => $child ? $child->openFollowUps()->orderBy('due_date')->get() : collect(),
            'blogs' => Blog::published()->latest()->take(3)->get(),
        ]);
    }
}
