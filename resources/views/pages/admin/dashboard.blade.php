@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $levels = $m['levels'];
    $total = max(1, $m['total_children']);
    $fu = $m['followups'];
    $out = $m['outcomes'];
    $evaluated = array_sum($out);
    $pct = fn($n) => round($n / $total * 100);
@endphp

@section('content')
    <div class="adm-head">
        <div>
            <h1>Ringkasan layanan</h1>
            <p>Kondisi pemantauan per {{ now()->translatedFormat('d F Y') }}</p>
        </div>
        <div class="adm-actions">
            <a class="adm-btn" href="{{ route('admin.stunting.index') }}"><i class="bi bi-bar-chart-line"></i> Laporan stunting</a>
            <a class="adm-btn is-primary" href="{{ route('admin.rekap') }}"><i class="bi bi-download"></i> Unduh rekap</a>
        </div>
    </div>

    <section class="adm-panel adm-kpis" aria-label="Angka utama">
        <div class="adm-kpi">
            <div class="adm-kpi-label">Anak terpantau</div>
            <div class="adm-kpi-value">{{ number_format($m['total_children']) }}</div>
            <div class="adm-kpi-sub">{{ $levels['belum'] }} belum dinilai</div>
        </div>
        <div class="adm-kpi is-high">
            <div class="adm-kpi-label">Prioritas tinggi</div>
            <div class="adm-kpi-value">{{ $levels['tinggi'] }}</div>
            <div class="adm-kpi-sub">{{ $levels['sedang'] }} prioritas sedang</div>
        </div>
        <div class="adm-kpi">
            <div class="adm-kpi-label">Pemantauan terlewat</div>
            <div class="adm-kpi-value">{{ $m['missed'] }}</div>
            <div class="adm-kpi-sub">melewati jadwal pengukuran</div>
        </div>
        <div class="adm-kpi">
            <div class="adm-kpi-label">Tindak lanjut selesai</div>
            <div class="adm-kpi-value">
                @if ($fu['completion_rate'] !== null){{ $fu['completion_rate'] }}<small>%</small>@else<small>-</small>@endif
            </div>
            <div class="adm-kpi-sub">
                {{ $fu['done'] }} dari {{ $fu['total'] }}@if ($fu['overdue'] > 0), {{ $fu['overdue'] }} terlambat @endif
            </div>
        </div>
    </section>

    <div class="adm-grid">
        <section class="adm-panel" aria-labelledby="h-top">
            <div class="adm-panel-head">
                <h2 id="h-top">Perlu ditinjau lebih dulu</h2>
                <a href="{{ route('admin.children.index', ['level' => 'tinggi']) }}">Lihat semua &rarr;</a>
            </div>
            @if ($m['top']->isEmpty())
                <div class="adm-empty">Belum ada anak dengan skor prioritas di atas nol.</div>
            @else
                <div class="adm-scroll">
                    <table class="adm-table">
                        <thead>
                            <tr><th>Anak</th><th>Faktor utama</th><th>Prioritas</th><th class="num">Skor</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($m['top'] as $child)
                                @php $a = $child->latestAssessment; @endphp
                                <tr>
                                    <td>
                                        <a class="adm-link" href="{{ route('admin.children.show', $child) }}">{{ $child->name }}</a>
                                        <span class="sub">{{ $child->ageInMonths() }} bulan
                                            @if ($child->latestMeasurement?->measured_at) &middot; diukur {{ $child->latestMeasurement->measured_at->translatedFormat('d M') }} @endif
                                        </span>
                                    </td>
                                    <td>{{ $a->factors[0]['label'] ?? '-' }}
                                        @if (count($a->factors) > 1)<span class="sub">dan {{ count($a->factors) - 1 }} faktor lain</span>@endif
                                    </td>
                                    <td><x-level :level="$a->level" /></td>
                                    <td class="num">{{ $a->score }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="adm-stack">
            <section class="adm-panel" aria-labelledby="h-dist">
                <div class="adm-panel-head"><h2 id="h-dist">Sebaran prioritas</h2></div>
                <div class="adm-panel-body">
                    <div class="dist-bar" role="img"
                        aria-label="Tinggi {{ $levels['tinggi'] }}, sedang {{ $levels['sedang'] }}, rendah {{ $levels['rendah'] }}, belum dinilai {{ $levels['belum'] }}">
                        @foreach (['tinggi', 'sedang', 'rendah', 'belum'] as $k)
                            @if ($levels[$k] > 0)<span class="is-{{ $k }}" style="width: {{ $levels[$k] / $total * 100 }}%"></span>@endif
                        @endforeach
                    </div>
                    <ul class="dist-list">
                        @foreach (['tinggi', 'sedang', 'rendah', 'belum'] as $k)
                            <li>
                                <x-level :level="$k" />
                                <span><b>{{ $levels[$k] }}</b><em>{{ $pct($levels[$k]) }}%</em></span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            <section class="adm-panel" aria-labelledby="h-out">
                <div class="adm-panel-head">
                    <h2 id="h-out">Hasil tindak lanjut</h2>
                    <span>{{ $evaluated }} dievaluasi</span>
                </div>
                <div class="adm-panel-body">
                    @if ($evaluated === 0)
                        <p class="adm-note">Belum ada tindak lanjut yang memiliki pemantauan sesudahnya.</p>
                    @else
                        <div class="outcome">
                            <div><strong style="color: var(--low)">{{ $out['membaik'] }}</strong><span>Membaik</span></div>
                            <div><strong>{{ $out['tetap'] }}</strong><span>Tetap</span></div>
                            <div><strong style="color: var(--high)">{{ $out['memburuk'] }}</strong><span>Memburuk</span></div>
                        </div>
                    @endif
                    <hr class="my-3" style="border-color: var(--line-soft)">
                    <p class="adm-note">
                        @if ($fu['on_time_rate'] !== null)
                            {{ $fu['on_time_rate'] }}% tindak lanjut selesai sebelum atau pada tenggat.
                        @else
                            Ketepatan waktu dihitung setelah ada tindak lanjut yang selesai.
                        @endif
                    </p>
                </div>
            </section>
        </div>
    </div>

    <div class="adm-grid is-even">
        <section class="adm-panel" aria-labelledby="h-m1">
            <div class="adm-panel-head"><h2 id="h-m1">Pengukuran per bulan</h2><span>6 bulan terakhir</span></div>
            <div class="adm-panel-body"><div class="chart-box"><canvas id="chartMeasure"></canvas></div></div>
        </section>
        <section class="adm-panel" aria-labelledby="h-m2">
            <div class="adm-panel-head"><h2 id="h-m2">Tindak lanjut dibuat dan selesai</h2><span>6 bulan terakhir</span></div>
            <div class="adm-panel-body"><div class="chart-box"><canvas id="chartFollow"></canvas></div></div>
        </section>
    </div>

    <p class="adm-note">
        Skor prioritas adalah alat bantu untuk menentukan urutan peninjauan, bukan diagnosis.
        Skrining KPSP bulan ini: {{ $kpspThisMonth }}.
    </p>
@endsection

@push('addon-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var data = @json($m['monthly']);
            var ink = '#0f172a', muted = '#64748b', grid = '#f1f5f9';
            Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = muted;

            function base(extra) {
                return Object.assign({
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: ink, padding: 8, cornerRadius: 4 } },
                    scales: {
                        x: { grid: { display: false }, border: { color: '#e2e8f0' } },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid }, border: { display: false } }
                    }
                }, extra || {});
            }

            new Chart(document.getElementById('chartMeasure'), {
                type: 'bar',
                data: { labels: data.labels, datasets: [{ label: 'Pengukuran', data: data.measurements, backgroundColor: '#0f172a', borderRadius: 2, maxBarThickness: 34 }] },
                options: base()
            });

            new Chart(document.getElementById('chartFollow'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label: 'Dibuat', data: data.created, backgroundColor: '#cbd5e1', borderRadius: 2, maxBarThickness: 22 },
                        { label: 'Selesai', data: data.done, backgroundColor: '#16a34a', borderRadius: 2, maxBarThickness: 22 }
                    ]
                },
                options: base({ plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, usePointStyle: false } }, tooltip: { backgroundColor: ink, padding: 8, cornerRadius: 4 } } })
            });
        })();
    </script>
@endpush
