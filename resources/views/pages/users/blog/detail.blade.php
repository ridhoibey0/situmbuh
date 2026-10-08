@extends('layouts.users')

@section('title', $blog->title . ' - Situmbuh')

@section('content')
    <a href="{{ route('blog.index') }}" class="u-back">&larr; Artikel</a>
    <h1 class="u-h1" style="font-size: 24px; line-height: 1.25">{{ $blog->title }}</h1>
    <p class="u-lead mb-3">{{ $blog->created_at->translatedFormat('d F Y') }}</p>

    @if ($blog->image)
        <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}" class="img-fluid mb-3" style="border-radius: 10px; width: 100%">
    @endif

    <div class="blog-content">{!! $blog->content !!}</div>
@endsection
