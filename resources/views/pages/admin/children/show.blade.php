@extends('layouts.admin')

@section('title', $child->name)

@section('content')
    <div class="adm-head">
        <div>
            <a href="{{ route('admin.children.index') }}" class="adm-note text-decoration-none">&larr; Daftar anak</a>
            <h1>{{ $child->name }}</h1>
            <p>
                {{ $child->gender === 'male' ? 'Laki-laki' : 'Perempuan' }} &middot;
                lahir {{ $child->bod->translatedFormat('d F Y') }} ({{ $child->ageInMonths() }} bulan)
                @if ($child->registeredBy) &middot; didaftarkan {{ $child->registeredBy->name }} @endif
            </p>
        </div>
        <div class="adm-actions">
            <a class="adm-btn" href="{{ route('admin.stunting.show', $child) }}"><i class="bi bi-graph-up"></i> Riwayat pertumbuhan</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="adm-grid">
        <section class="adm-panel" aria-labelledby="h-risk">
            <div class="adm-panel-head"><h2 id="h-risk">Prioritas dan alasannya</h2></div>
            <div class="adm-panel-body">
                <x-risk-factors :assessment="$child->latestAssessment" />
            </div>
        </section>

        <div class="adm-stack">
            <section class="adm-panel" aria-labelledby="h-staff">
                <div class="adm-panel-head"><h2 id="h-staff">Petugas</h2><span>{{ $child->staff->count() }} orang</span></div>
                <div class="adm-panel-body">
                    @forelse ($child->staff as $staff)
                        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--line-soft)">
                            <div>
                                <div class="fw-semibold" style="color: var(--ink)">{{ $staff->name }}</div>
                                <div class="adm-note">{{ $staff->roles?->label() }}</div>
                            </div>
                            <form method="POST" action="{{ route('admin.children.staff.remove', [$child, $staff]) }}"
                                onsubmit="return confirm('Cabut penugasan {{ $staff->name }}?')">
                                @csrf @method('DELETE')
                                <button class="adm-btn" type="submit">Cabut</button>
                            </form>
                        </div>
                    @empty
                        <p class="adm-note mb-2">Belum ada kader atau tenaga kesehatan yang ditugaskan.</p>
                    @endforelse

                    <form method="POST" action="{{ route('admin.children.staff.assign', $child) }}" class="d-flex gap-2 mt-3">
                        @csrf
                        <select name="user_id" class="form-select" aria-label="Pilih petugas" required>
                            <option value="" disabled selected>Pilih petugas</option>
                            @foreach ($assignable as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->roles->label() }})</option>
                            @endforeach
                        </select>
                        <button class="adm-btn is-primary" type="submit" @disabled($assignable->isEmpty())>Tugaskan</button>
                    </form>
                    @error('user_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </section>

            <section class="adm-panel" aria-labelledby="h-parent">
                <div class="adm-panel-head"><h2 id="h-parent">Orang tua</h2></div>
                <div class="adm-panel-body">
                    @if ($child->parent)
                        <div class="fw-semibold" style="color: var(--ink)">{{ $child->parent->parent_name }}</div>
                        <div class="adm-note mb-2">{{ $child->parent->phone }}</div>
                        <form method="POST" action="{{ route('admin.children.parent.unlink', $child) }}"
                            onsubmit="return confirm('Lepas tautan orang tua?')">
                            @csrf @method('DELETE')
                            <button class="adm-btn" type="submit">Lepas tautan</button>
                        </form>
                    @else
                        <p class="adm-note mb-2">
                            @if ($child->parent_phone)
                                Menunggu akun dengan nomor <strong>{{ $child->parent_phone }}</strong>. Anak tertaut otomatis saat akun dibuat.
                            @else
                                Belum tertaut ke akun orang tua.
                            @endif
                        </p>
                        <form method="POST" action="{{ route('admin.children.parent.link', $child) }}" class="d-flex gap-2">
                            @csrf @method('PUT')
                            <input type="tel" name="phone" class="form-control" placeholder="No. HP akun orang tua"
                                value="{{ old('phone', $child->parent_phone) }}" required>
                            <button class="adm-btn is-primary" type="submit">Tautkan</button>
                        </form>
                        @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
