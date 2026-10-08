@extends('layouts.users')

@section('title', 'Skrining KPSP - Situmbuh')

@php
    $items = [
        [1, 'bi-heart-pulse', 'Pra skrining perkembangan', 'Perkembangan umum sesuai usia'],
        [2, 'bi-ear', 'Deteksi dini gangguan pendengaran', 'Pemeriksaan pendengaran'],
        [3, 'bi-eye', 'Deteksi dini kelainan pupil putih', 'Pemeriksaan mata'],
        [4, 'bi-eyeglasses', 'Deteksi dini gangguan penglihatan', 'Pemeriksaan penglihatan'],
        [5, 'bi-emoji-neutral', 'Deteksi dini masalah perilaku dan emosi', 'Perilaku dan emosi anak'],
        [6, 'bi-puzzle', 'Deteksi dini gangguan spektrum autisme', 'Perilaku sosial dan komunikasi'],
    ];
@endphp

@section('content')
    <div class="u-head">
        <h1 class="u-h1">Skrining perkembangan</h1>
        <p class="u-lead">
            Pilih jenis skrining. Pertanyaan menyesuaikan usia
            @if (!empty($activeChild)){{ $activeChild->name }} ({{ $activeChild->ageInMonths() }} bulan)@else anak @endif.
        </p>
    </div>

    <div class="u-list">
        @foreach ($items as [$id, $icon, $title, $desc])
            <a class="u-row" href="{{ route('question.index', $id) }}">
                <i class="bi {{ $icon }} lead-icon"></i>
                <div class="grow"><div class="t">{{ $title }}</div><div class="d">{{ $desc }}</div></div>
                <i class="bi bi-chevron-right chev"></i>
            </a>
        @endforeach
    </div>

    <p class="u-muted mt-3" style="font-size: 12.5px">
        Hasil skrining adalah informasi pendukung dan bukan diagnosis. Konsultasikan hasilnya dengan tenaga kesehatan.
    </p>
@endsection
