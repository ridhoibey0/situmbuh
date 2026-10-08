@extends('layouts.admin')

@section('title', 'Laporan stunting')

@section('content')
    <div class="adm-head">
        <div>
            <h1>Laporan stunting</h1>
            <p>Anak dengan Z-score TB/U di bawah -2 SD pada pengukuran terakhir (standar WHO).</p>
        </div>
    </div>

    <section class="adm-panel adm-kpis" style="grid-template-columns: repeat(3, 1fr)" aria-label="Ringkasan">
        <div class="adm-kpi">
            <div class="adm-kpi-label">Total</div>
            <div class="adm-kpi-value">{{ $summary['total'] }}</div>
            <div class="adm-kpi-sub">anak di bawah -2 SD</div>
        </div>
        <div class="adm-kpi is-high">
            <div class="adm-kpi-label">Sangat pendek</div>
            <div class="adm-kpi-value">{{ $summary['sangat_pendek'] }}</div>
            <div class="adm-kpi-sub">Z di bawah -3 SD</div>
        </div>
        <div class="adm-kpi">
            <div class="adm-kpi-label">Pendek</div>
            <div class="adm-kpi-value">{{ $summary['pendek'] }}</div>
            <div class="adm-kpi-sub">Z -3 sampai di bawah -2 SD</div>
        </div>
    </section>

    <section class="adm-panel">
        <form method="GET" action="{{ route('admin.stunting.index') }}" class="adm-panel-body d-flex flex-wrap gap-2 align-items-center"
            style="border-bottom: 1px solid var(--line-soft)">
            <input type="search" name="search" value="{{ $filters['search'] }}" class="form-control" style="max-width: 260px"
                placeholder="Cari nama anak atau desa" aria-label="Cari nama anak atau desa">
            <select name="village_id" class="form-select" style="max-width: 200px" aria-label="Desa">
                <option value="">Semua desa</option>
                @foreach ($villages as $village)
                    <option value="{{ $village->id }}" @selected($filters['village_id'] == $village->id)>{{ $village->name }}</option>
                @endforeach
            </select>
            <select name="severity" class="form-select" style="max-width: 170px" aria-label="Tingkat">
                <option value="">Semua tingkat</option>
                <option value="sangat_pendek" @selected($filters['severity'] === 'sangat_pendek')>Sangat pendek</option>
                <option value="pendek" @selected($filters['severity'] === 'pendek')>Pendek</option>
            </select>
            <button type="submit" class="adm-btn is-primary">Terapkan</button>
            @if ($filters['search'] || $filters['village_id'] || $filters['severity'])
                <a href="{{ route('admin.stunting.index') }}" class="adm-btn">Atur ulang</a>
            @endif
        </form>

        @if ($children->count() > 0)
            <div class="adm-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Anak</th>
                            <th>Desa</th>
                            <th class="num">Usia</th>
                            <th class="num">TB (cm)</th>
                            <th class="num">BB (kg)</th>
                            <th class="num">Z TB/U</th>
                            <th>Status</th>
                            <th>Diukur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($children as $child)
                            @php $severe = $child['status'] === 'Sangat Pendek'; @endphp
                            <tr>
                                <td>
                                    <a class="adm-link" href="{{ route('admin.stunting.show', $child['id']) }}">{{ $child['name'] }}</a>
                                    <span class="sub">{{ $child['gender'] === 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                                </td>
                                <td>{{ $child['village'] }}</td>
                                <td class="num">{{ $child['age_in_months'] }} bln</td>
                                <td class="num">{{ number_format($child['height'], 1) }}</td>
                                <td class="num">{{ $child['weight'] !== null ? number_format($child['weight'], 1) : '-' }}</td>
                                <td class="num" style="color: {{ $severe ? 'var(--high)' : 'var(--mid)' }}; font-weight: 700">{{ number_format($child['z_tb'], 2) }}</td>
                                <td><span class="lvl {{ $severe ? 'is-tinggi' : 'is-sedang' }}">{{ $child['status'] }}</span></td>
                                <td>{{ $child['measured_at']->translatedFormat('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="adm-panel-body d-flex justify-content-between align-items-center" style="border-top: 1px solid var(--line-soft)">
                <span class="adm-note">Menampilkan {{ $children->firstItem() ?? 0 }}-{{ $children->lastItem() ?? 0 }} dari {{ $children->total() }} anak</span>
                <div>{{ $children->links() }}</div>
            </div>
        @else
            <div class="adm-empty">Tidak ada anak yang memenuhi kriteria.</div>
        @endif
    </section>
@endsection
