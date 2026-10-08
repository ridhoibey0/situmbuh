@extends('layouts.users')

@section('title', 'Data anak - Situmbuh')

@section('content')
    <div class="u-head d-flex justify-content-between align-items-end">
        <div>
            <h1 class="u-h1">Data anak</h1>
            <p class="u-lead">{{ $children->count() }} anak terdaftar</p>
        </div>
        <a href="{{ route('children.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="u-list">
        @foreach ($children as $child)
            @php $isActive = $child->id === $activeId; @endphp
            <div class="u-row" style="{{ $isActive ? 'background: var(--accent-wash)' : '' }}">
                <div class="grow">
                    <div class="t">{{ $child->name }}</div>
                    <div class="d">
                        {{ $child->gender === 'male' ? 'Laki-laki' : 'Perempuan' }} &middot; {{ $child->ageInMonths() }} bulan
                        @if ($child->latestMeasurement?->measured_at)
                            <br>Diukur {{ $child->latestMeasurement->measured_at->translatedFormat('d M Y') }}
                        @endif
                    </div>
                </div>
                @if ($isActive)
                    <span class="u-small fw-semibold" style="color: var(--accent-ink)">Aktif</span>
                @else
                    <form method="POST" action="{{ route('children.select', $child) }}">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm">Pilih</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
@endsection
