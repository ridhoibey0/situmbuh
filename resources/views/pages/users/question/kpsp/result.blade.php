@extends('layouts.users')

@section('title', 'Hasil skrining - Situmbuh')

@php
    $i = $result->interpretation;
    $tone = match (true) {
        $i === 'Sesuai umur', str_contains($i, 'baik'), str_contains($i, 'Risiko rendah'), $i === 'Normal' => 'is-rendah',
        $i === 'Meragukan', str_contains($i, 'meragukan') => 'is-sedang',
        default => 'is-tinggi',
    };
@endphp

@section('content')
    <div class="u-head">
        <h1 class="u-h1">Hasil skrining</h1>
        @if (!empty($result->child))<p class="u-lead">{{ $result->child->name }} &middot; {{ $result->created_at->translatedFormat('d M Y') }}</p>@endif
    </div>

    <section class="u-panel">
        <span class="lvl {{ $tone }}" style="font-size: 17px; white-space: normal">{{ $result->interpretation }}</span>
        <p class="mt-2 mb-0" style="color: var(--ink-soft)">{{ $result->intervensi }}</p>
        <hr class="u-divider">
        <p class="u-small u-muted mb-0">Hasil skrining adalah informasi pendukung dan bukan diagnosis. Konsultasikan dengan kader atau tenaga kesehatan.</p>
    </section>

    <div class="d-grid gap-2 mt-3" style="grid-template-columns: 1fr 1fr">
        <a href="{{ route('questioner.index') }}" class="btn btn-outline-secondary">Skrining lain</a>
        <a href="{{ route('users.index') }}" class="btn btn-primary">Ke beranda</a>
    </div>

    @include('partials.users.testimonial-modal', ['show' => $tampilkanModalTestimoni])
@endsection
