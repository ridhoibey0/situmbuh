@extends('layouts.admin')

@section('title', 'Anak dan penugasan')

@section('content')
    <div class="adm-head">
        <div>
            <h1>Anak dan penugasan</h1>
            <p>{{ $children->total() }} anak{{ $search !== '' || $level || $unassigned ? ' sesuai filter' : '' }}</p>
        </div>
    </div>

    <section class="adm-panel">
        <form method="GET" class="adm-panel-body d-flex flex-wrap gap-2 align-items-center" style="border-bottom: 1px solid var(--line-soft)">
            <input type="search" name="search" value="{{ $search }}" class="form-control" style="max-width: 260px"
                placeholder="Cari nama anak" aria-label="Cari nama anak">
            <select name="level" class="form-select" style="max-width: 190px" aria-label="Filter prioritas" onchange="this.form.submit()">
                <option value="">Semua prioritas</option>
                @foreach (['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah'] as $value => $label)
                    <option value="{{ $value }}" @selected($level === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="unassigned" value="1" id="unassigned" @checked($unassigned)
                    onchange="this.form.submit()">
                <label class="form-check-label fw-normal" for="unassigned">Belum ada petugas</label>
            </div>
            <button class="adm-btn ms-auto" type="submit">Terapkan</button>
        </form>

        <div class="adm-scroll">
            <table class="adm-table">
                <thead>
                    <tr><th>Anak</th><th>Orang tua</th><th>Petugas</th><th>Prioritas</th><th>Terakhir diukur</th></tr>
                </thead>
                <tbody>
                    @forelse ($children as $child)
                        <tr>
                            <td>
                                <a class="adm-link" href="{{ route('admin.children.show', $child) }}">{{ $child->name }}</a>
                                <span class="sub">{{ $child->gender === 'male' ? 'Laki-laki' : 'Perempuan' }} &middot; {{ $child->ageInMonths() }} bulan</span>
                            </td>
                            <td>
                                @if ($child->parent)
                                    {{ $child->parent->parent_name }}<span class="sub">{{ $child->parent->phone }}</span>
                                @elseif ($child->parent_phone)
                                    <span class="text-muted">Belum punya akun</span><span class="sub">{{ $child->parent_phone }}</span>
                                @else
                                    <span class="text-muted">Belum tertaut</span>
                                @endif
                            </td>
                            <td>
                                @forelse ($child->staff as $staff)
                                    <span class="d-block">{{ $staff->name }}</span>
                                @empty
                                    <span class="text-muted">Belum ada</span>
                                @endforelse
                            </td>
                            <td><x-level :level="$child->latestAssessment?->level" /></td>
                            <td>
                                @if ($child->latestMeasurement?->measured_at)
                                    {{ $child->latestMeasurement->measured_at->translatedFormat('d M Y') }}
                                @else
                                    <span class="text-muted">Belum pernah</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="adm-empty">Tidak ada anak yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-3">{{ $children->links() }}</div>
@endsection
