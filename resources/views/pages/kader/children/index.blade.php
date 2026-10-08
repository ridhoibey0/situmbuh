@extends('layouts.users')

@section('title', 'Anak yang ditangani - Situmbuh')

@section('content')
    <div class="u-head">
        <h1 class="u-h1">Anak yang ditangani</h1>
        <p class="u-lead">Diurutkan dari prioritas tertinggi</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="u-stats mb-3" aria-label="Ringkasan">
        <a class="u-stat is-high" href="{{ route('kader.children.index', ['level' => 'tinggi']) }}"><b>{{ $summary['tinggi'] }}</b><span>Prioritas tinggi</span></a>
        <a class="u-stat is-mid" href="{{ route('kader.children.index', ['level' => 'sedang']) }}"><b>{{ $summary['sedang'] }}</b><span>Prioritas sedang</span></a>
        <a class="u-stat" href="{{ route('kader.children.index', ['missed' => 1]) }}"><b>{{ $summary['missed'] }}</b><span>Belum diukur</span></a>
        <a class="u-stat" href="{{ route('kader.follow-ups.index') }}"><b>{{ $summary['overdue_followups'] }}</b><span>Tindak lanjut terlambat</span></a>
    </div>

    @if ($missed)
        <div class="alert alert-secondary py-2 u-small">
            Menampilkan anak yang pemantauannya terlewat. <a href="{{ route('kader.children.index') }}">Tampilkan semua</a>
        </div>
    @endif

    <form method="GET" class="u-filter">
        <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama anak" aria-label="Cari nama anak">
        <select name="level" class="form-select" style="max-width: 128px" aria-label="Filter prioritas" onchange="this.form.submit()">
            <option value="">Semua</option>
            @foreach (['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah'] as $value => $label)
                <option value="{{ $value }}" @selected($level === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <div class="u-list">
        @forelse ($children as $child)
            @php
                $a = $child->latestAssessment;
                $last = $child->latestMeasurement?->measured_at;
            @endphp
            <a href="{{ route('kader.children.show', $child) }}" class="u-row align-items-start">
                <div class="grow">
                    <div class="d-flex justify-content-between align-items-baseline gap-2">
                        <span class="t">{{ $child->name }}</span>
                        <x-risk-badge :level="$a?->level" />
                    </div>
                    <div class="d">
                        {{ $child->gender === 'male' ? 'Laki-laki' : 'Perempuan' }} &middot; {{ $child->ageInMonths() }} bulan
                        @if ($child->parent) &middot; {{ $child->parent->parent_name }} @endif
                    </div>
                    <div class="d" style="{{ $last ? '' : 'color: var(--high)' }}">
                        {{ $last ? 'Diukur ' . $last->translatedFormat('d M Y') : 'Belum pernah diukur' }}
                    </div>
                    @if ($a && count($a->factors))
                        <div class="d mt-1" style="color: var(--ink-soft)">
                            {{ $a->factors[0]['label'] }}@if (count($a->factors) > 1), +{{ count($a->factors) - 1 }} lainnya @endif
                        </div>
                    @endif
                </div>
            </a>
        @empty
            <div class="u-row"><div class="d">Tidak ada anak yang cocok.</div></div>
        @endforelse
    </div>

    <div class="mt-3 d-flex justify-content-center">{{ $children->links() }}</div>
@endsection
