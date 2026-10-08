<?php

namespace App\Http\Controllers\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::latest()->paginate(4);
        return view('pages.users.blog.index', compact('blogs'));
    }
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->first();
        if (!$blog) {
            return abort(404);
        }
        return view('pages.users.blog.detail', compact('blog'));
    }
}
