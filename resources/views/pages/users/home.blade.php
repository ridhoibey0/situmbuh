@extends('layouts.users')

@section('title', 'Beranda - Situmbuh')

@php
    $user = auth()->user();
    $greetName = \Illuminate\Support\Str::of($user->parent_name ?: $user->name)->before(' ');
    $status = [
        'tinggi' => ['is-tinggi', 'Perlu perhatian segera'],
        'sedang' => ['is-sedang', 'Perlu dipantau'],
        'rendah' => ['is-rendah', 'Kondisi baik'],
    ][$assessment?->level] ?? ['is-belum', 'Belum ada penilaian'];
    $lastAt = $child?->latestMeasurement?->measured_at;
    $days = $lastAt ? (int) $lastAt->diffInDays(now()) : null;
    $overdue = collect($assessment?->factors ?? [])->contains(fn($f) => str_starts_with($f['code'], 'monitoring_'));
@endphp

@section('hero')
    <section class="hero">
         <h1>Halo, {{ $greetName }}!</h1>
        <p>{{ now()->translatedFormat('l, d F Y') }}</p>
    </section>
@endsection

@section('content')
    @if ($child)
        <section class="u-panel hero-card" aria-label="Anak aktif">
            <div class="d-flex align-items-center gap-3">
                <x-child-avatar :gender="$child->gender" style="width: 60px; height: 60px" />
                <div class="grow" style="min-width: 0">
                    <div class="fw-bold" style="color: var(--ink); font-size: 18px; line-height: 1.2">{{ $child->name }}</div>
                    <div class="u-small u-muted">{{ $child->ageInMonths() }} bulan &middot; {{ $child->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</div>
                </div>
            </div>
            <div class="mt-3"><span class="lvl {{ $status[0] }}" style="font-size: 14px">{{ $status[1] }}</span></div>
            <p class="u-small u-muted mb-0 mt-2">
                @if ($lastAt)
                    Terakhir diukur {{ $lastAt->translatedFormat('d M Y') }}
                    ({{ $days === 0 ? 'hari ini' : $days . ' hari lalu' }}).
                @else
                    Belum pernah diukur.
                @endif
                @if ($overdue)<strong style="color: var(--sun-ink)"> Sudah waktunya diukur kembali.</strong>@endif
            </p>
            <div class="d-grid gap-2 mt-3" style="grid-template-columns: 1fr 1fr">
                <a href="{{ route('measurement.create') }}" class="btn btn-primary">Catat pengukuran</a>
                <a href="{{ route('growth.index') }}" class="btn btn-outline-secondary">Lihat grafik</a>
            </div>
        </section>

        @if ($followUps->isNotEmpty())
            <p class="u-section">Tindak lanjut untuk {{ $child->name }}</p>
            <div class="u-list">
                @foreach ($followUps as $followUp)
                    <div class="u-row">
                        <i class="bi bi-flag lead-icon" style="background: var(--sun); color: var(--sun-ink)"></i>
                        <div class="grow">
                            <div class="t">{{ $followUp->action_type->label() }}</div>
                            <div class="d" style="{{ $followUp->isOverdue() ? 'color: var(--pink-ink); font-weight: 700' : '' }}">
                                Tenggat {{ $followUp->due_date->translatedFormat('d M Y') }}{{ $followUp->isOverdue() ? ' (terlewat)' : '' }}
                                &middot; {{ $followUp->status->label() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <section class="u-panel hero-card">
            <h2 class="u-panel-title">Mulai dengan data anak</h2>
            <p class="u-small u-muted">Tambahkan anak untuk mulai mencatat pengukuran dan memantau pertumbuhannya.</p>
            <a href="{{ route('children.create') }}" class="btn btn-primary w-100">Tambah anak</a>
        </section>
    @endif

    <p class="u-section">Menu</p>
    <div class="tiles">
        <a class="tile is-mint" href="{{ route('growth.index') }}"><i class="bi bi-graph-up-arrow"></i><b>Pertumbuhan</b><span>Grafik WHO dan status</span></a>
        <a class="tile is-sun" href="{{ route('questioner.index') }}"><i class="bi bi-clipboard2-pulse"></i><b>Skrining KPSP</b><span>Sesuai usia anak</span></a>
        <a class="tile is-sky" href="{{ route('blog.index') }}"><i class="bi bi-journal-richtext"></i><b>Artikel</b><span>Informasi tumbuh kembang</span></a>
        <a class="tile is-pink" href="/users/chat-ai"><i class="bi bi-chat-heart"></i><b>Tanya asisten</b><span>Tahu data anak Anda</span></a>
    </div>

    @if ($blogs->isNotEmpty())
        <div class="d-flex justify-content-between align-items-baseline">
            <p class="u-section">Artikel terbaru</p>
            <a class="u-small fw-bold" href="{{ route('blog.index') }}" style="color: var(--accent)">Semua</a>
        </div>
        <div class="u-list">
            @foreach ($blogs as $blog)
                <a class="u-row" href="{{ route('detail.blog', $blog->slug) }}">
                    <span class="thumb" style="background-image: url('{{ $blog->image ? asset('storage/' . $blog->image) : '' }}')"></span>
                    <div class="grow"><div class="t">{{ $blog->title }}</div><div class="d">{{ $blog->created_at->translatedFormat('d M Y') }}</div></div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
