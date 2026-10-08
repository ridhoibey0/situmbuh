@extends('layouts.admin')

@section('title', $child->name)

@php
    $statusClass = fn($s) => $s === 'Sangat Pendek' ? 'is-tinggi' : ($s === 'Pendek' ? 'is-sedang' : 'is-rendah');
    $fmt = fn($v, $d = 1) => $v !== null ? number_format((float) $v, $d) : '-';
@endphp

@section('content')
    <div class="adm-head">
        <div>
            <a href="{{ route('admin.stunting.index') }}" class="adm-note text-decoration-none">&larr; Laporan stunting</a>
            <h1>{{ $child->name }}</h1>
            <p>
                {{ $child->gender === 'male' ? 'Laki-laki' : 'Perempuan' }} &middot;
                lahir {{ \Carbon\Carbon::parse($child->bod)->translatedFormat('d F Y') }}
                @if ($child->parent?->parent_name) &middot; orang tua {{ $child->parent->parent_name }} @endif
                @if ($child->parent?->phone) ({{ $child->parent->phone }}) @endif
                <br>{{ $village !== '-' ? $village . ($district !== '-' ? ', Kec. ' . $district : '') : 'Wilayah belum diisi' }}
            </p>
        </div>
        <div class="adm-actions">
            @if ($latest)<span class="lvl {{ $statusClass($latest['status']) }}" style="font-size: 15px">{{ $latest['status'] }}</span>@endif
            <a class="adm-btn" href="{{ route('admin.children.show', $child) }}">Prioritas dan penugasan</a>
        </div>
    </div>

    @if ($latest)
        <section class="adm-panel adm-kpis" style="grid-template-columns: repeat(3, 1fr)" aria-label="Pengukuran terakhir">
            <div class="adm-kpi">
                <div class="adm-kpi-label">Tinggi badan</div>
                <div class="adm-kpi-value">{{ $fmt($latest['height']) }}<small> cm</small></div>
                <div class="adm-kpi-sub">Z TB/U {{ $fmt($latest['z_tb'], 2) }}</div>
            </div>
            <div class="adm-kpi">
                <div class="adm-kpi-label">Berat badan</div>
                <div class="adm-kpi-value">{{ $fmt($latest['weight']) }}<small> kg</small></div>
                <div class="adm-kpi-sub">Z BB/U {{ $fmt($latest['z_bb'], 2) }}</div>
            </div>
            <div class="adm-kpi">
                <div class="adm-kpi-label">Diukur</div>
                <div class="adm-kpi-value" style="font-size: 22px; line-height: 1.6">{{ $latest['measured_at']->translatedFormat('d M Y') }}</div>
                <div class="adm-kpi-sub">usia {{ $latest['age_in_months'] }} bulan &middot; LK {{ $fmt($latest['head_circumference']) }} &middot; LiLA {{ $fmt($latest['arm_circumference']) }}</div>
            </div>
        </section>
    @endif

    <section class="adm-panel">
        <div class="adm-panel-head">
            <h2>Tren pertumbuhan</h2>
            <span>Z-score TB/U dan tinggi badan</span>
        </div>
        <div class="adm-panel-body">
            @if ($history->count() >= 2)
                <div class="chart-box" style="height: 280px"><canvas id="trendChart"></canvas></div>
            @else
                <p class="adm-note mb-0">Minimal dua pengukuran diperlukan untuk menampilkan tren.</p>
            @endif
        </div>
    </section>

    <section class="adm-panel">
        <div class="adm-panel-head"><h2>Riwayat pengukuran</h2><span>{{ $history->count() }} pengukuran</span></div>
        @if ($history->count() > 0)
            <div class="adm-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th><th class="num">Usia</th><th class="num">TB</th><th class="num">BB</th>
                            <th class="num">LK</th><th class="num">LiLA</th><th class="num">Z TB/U</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history->reverse() as $row)
                            <tr>
                                <td>{{ $row['measured_at']->translatedFormat('d M Y') }}</td>
                                <td class="num">{{ $row['age_in_months'] }} bln</td>
                                <td class="num">{{ $fmt($row['height']) }}</td>
                                <td class="num">{{ $fmt($row['weight']) }}</td>
                                <td class="num">{{ $fmt($row['head_circumference']) }}</td>
                                <td class="num">{{ $fmt($row['arm_circumference']) }}</td>
                                <td class="num" style="font-weight: 700">{{ $fmt($row['z_tb'], 2) }}</td>
                                <td><span class="lvl {{ $statusClass($row['status']) }}">{{ $row['status'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="adm-empty">Belum ada riwayat pengukuran.</div>
        @endif
    </section>
@endsection

@push('addon-script')
    @if ($history->count() >= 2)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
                Chart.defaults.font.size = 12;
                Chart.defaults.color = '#64748b';
                var labels = @json($history->map(fn($r) => $r['measured_at']->translatedFormat('d M Y'))->values());
                var z = @json($history->map(fn($r) => $r['z_tb'])->values());
                var h = @json($history->map(fn($r) => (float) $r['height'])->values());

                new Chart(document.getElementById('trendChart'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Z-score TB/U', data: z, borderColor: '#dc2626', backgroundColor: '#dc2626', tension: .25, yAxisID: 'y', pointRadius: 3 },
                            { label: 'Tinggi (cm)', data: h, borderColor: '#0f172a', backgroundColor: '#0f172a', tension: .25, yAxisID: 'y1', borderDash: [5, 4], pointRadius: 3 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10 } }, tooltip: { backgroundColor: '#0f172a', padding: 8, cornerRadius: 4 } },
                        scales: {
                            x: { grid: { display: false } },
                            y: { title: { display: true, text: 'Z-score' }, suggestedMin: -4, suggestedMax: 1, grid: { color: '#f1f5f9' } },
                            y1: { position: 'right', title: { display: true, text: 'Tinggi (cm)' }, grid: { drawOnChartArea: false } }
                        }
                    }
                });
            })();
        </script>
    @endif
@endpush
