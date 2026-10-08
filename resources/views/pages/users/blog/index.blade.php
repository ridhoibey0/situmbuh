@extends('layouts.users')

@section('title', 'Artikel - Situmbuh')

@section('content')
    <div class="u-head">
        <h1 class="u-h1">Artikel</h1>
        <p class="u-lead">Informasi tumbuh kembang anak</p>
    </div>

    <div class="u-list">
        @forelse ($blogs as $blog)
            <a class="u-row align-items-start" href="{{ route('detail.blog', $blog->slug) }}">
                <span class="thumb" style="background-image: url('{{ $blog->image ? asset('storage/' . $blog->image) : '' }}')"></span>
                <div class="grow">
                    <div class="t">{{ $blog->title }}</div>
                    <div class="d">{{ \Illuminate\Support\Str::limit(strip_tags($blog->content), 90) }}</div>
                </div>
            </a>
        @empty
            <div class="u-row"><div class="d">Belum ada artikel.</div></div>
        @endforelse
    </div>

    <div class="mt-3 d-flex justify-content-center">{{ $blogs->links() }}</div>
@endsection
