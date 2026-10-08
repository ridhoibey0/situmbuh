@extends('layouts.users')

@section('title', 'Pertanyaan belum tersedia - Situmbuh')

@php $age = max(0, (int) $ageInMonths); @endphp

@section('content')
    <div class="u-head">
        <a href="{{ route('questioner.index') }}" class="u-back">&larr; Skrining</a>
        <h1 class="u-h1">Pertanyaan belum tersedia</h1>
    </div>

    <section class="u-panel">
        <p class="mb-2">Belum ada paket pertanyaan untuk usia <strong>{{ intdiv($age, 12) }} tahun {{ $age % 12 }} bulan</strong>.</p>
        <p class="u-small u-muted mb-3">Pantau pertumbuhan anak melalui menu Pertumbuhan, atau coba jenis skrining lain.</p>
        <a href="{{ route('questioner.index') }}" class="btn btn-primary w-100">Pilih skrining lain</a>
    </section>
@endsection
