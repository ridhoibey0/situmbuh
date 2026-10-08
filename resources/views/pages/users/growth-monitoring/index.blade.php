@extends('layouts.users')

@section('title', 'Pertumbuhan - Situmbuh')

@php
    $latest = collect($weightData)->whereNotNull('berat')->last();

    // kunci => [label singkat, satuan, bidang nilai, bidang z-score, bidang klasifikasi, label panjang, ikon]
    $params = [
        'weight' => ['Berat', 'kg', 'berat', 'zBB', 'klasifikasiBB', 'Berat badan menurut usia', 'bi-speedometer2'],
        'height' => ['Tinggi', 'cm', 'tinggi', 'zTB', 'klasifikasiTB', 'Tinggi badan menurut usia', 'bi-rulers'],
        'head' => ['L. kepala', 'cm', 'kepala', 'zLK', 'klasifikasiLK', 'Lingkar kepala menurut usia', 'bi-emoji-smile'],
        'arm' => ['L. lengan', 'cm', 'lengan', 'zLL', 'klasifikasiLL', 'Lingkar lengan menurut usia', 'bi-bandaid'],
    ];

    $tone = fn($status) => match (true) {
        in_array($status, ['Gizi Baik', 'Normal', 'Gizi Normal'], true) => 'is-rendah',
        in_array($status, ['Gizi Kurang', 'Pendek', 'Berisiko gizi lebih', 'Mikrosefali', 'Makrosefali'], true) => 'is-sedang',
        $status === null => 'is-belum',
        default => 'is-tinggi',
    };

    $ageNow = $child->bod->diff(now());
@endphp

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="u-head d-flex align-items-center gap-3">
        <x-child-avatar :gender="$child->gender" style="width: 52px; height: 52px" />
        <div>
            <h1 class="u-h1" style="font-size: 22px">{{ $child->name }}</h1>
            <p class="u-lead">{{ $ageNow->y }} th {{ $ageNow->m }} bln {{ $ageNow->d }} hr</p>
        </div>
    </div>

    <x-parent-summary :assessment="$assessment" :follow-ups="$followUps" />

    <p class="u-section">Grafik pertumbuhan</p>

    @if ($latest)
        <div class="m-cards" role="tablist" aria-label="Pilih ukuran">
            @foreach ($params as $key => [$label, $unit, $field, $zField, $statusField, $long, $icon])
                <button type="button" role="tab" data-key="{{ $key }}" class="m-card {{ $loop->first ? 'is-active' : '' }}">
                    <i class="bi {{ $icon }}"></i>
                    <small>{{ $label }}</small>
                    <b>{{ $latest[$field] ?? '-' }} <em>{{ $unit }}</em></b>
                </button>
            @endforeach
        </div>

        <section class="chart-card">
            <div class="title" id="chartTitle">Berat badan menurut usia</div>
            <div class="big"><span id="chartValue">{{ $latest['berat'] ?? '-' }}</span> <small id="chartUnit">kg</small> <small>/ {{ $latest['usia_bulan'] }} bulan</small></div>
            <div class="chart-inner">
                <div style="position: relative; height: 250px"><canvas id="growthChart" aria-label="Grafik pertumbuhan"></canvas></div>
                <div class="legend">
                    <span><i style="background: #bbf7d0"></i>Normal</span>
                    <span><i style="background: #fde68a"></i>Waspada</span>
                    <span><i style="background: #fecaca"></i>Di luar batas</span>
                    <span><i style="background: #0369a1"></i>{{ $child->name }}</span>
                </div>
            </div>
        </section>

        @foreach ($params as $key => [$label, $unit, $field, $zField, $statusField, $long])
            @php $status = $latest[$statusField] ?? null; $z = $latest[$zField] ?? null; @endphp
            <div class="u-panel mt-3 growth-info" data-key="{{ $key }}" @if (!$loop->first) hidden @endif>
                <div class="u-panel-head">
                    <h2 class="u-panel-title">{{ $long }}</h2>
                    <span>{{ \Carbon\Carbon::parse($latest['measured_at'])->translatedFormat('d M Y') }}</span>
                </div>
                <span class="lvl {{ $tone($status) }}" style="font-size: 14.5px">{{ $status ?? 'Belum ada acuan untuk usia ini' }}</span>
                <div class="d-flex gap-4 mt-3 u-small">
                    <div><div class="u-muted">Nilai</div><strong>{{ $latest[$field] ?? '-' }} {{ $unit }}</strong></div>
                    <div><div class="u-muted">Z-score</div><strong>{{ $z !== null ? number_format($z, 2) : '-' }}</strong></div>
                    <div><div class="u-muted">Usia</div><strong>{{ $latest['usia'] }}</strong></div>
                </div>
                <p class="u-small u-muted mt-3 mb-0">
                    Z-score menunjukkan jarak dari median WHO untuk usia dan jenis kelamin yang sama. Pita hijau adalah rentang normal.
                    Untuk penilaian lebih lanjut, konsultasikan dengan kader atau tenaga kesehatan.
                </p>
            </div>
        @endforeach
    @else
        <div class="u-panel"><p class="u-muted mb-0">Belum ada data pengukuran. Catat pengukuran pertama untuk melihat grafik.</p></div>
    @endif

    @include('partials.users.testimonial-modal', ['show' => $tampilkanModalTestimoni])
@endsection

@section('fab')
    <a href="{{ route('measurement.create') }}" class="fab"><i class="bi bi-plus-lg"></i> Catat pengukuran</a>
@endsection

@push('addon-script')
    @if ($latest)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                var rows = @json($weightData);
                var ref = @json($reference);
                var meta = {
                    weight: { field: 'berat', z: 'zBB', unit: 'kg', label: 'Berat', title: 'Berat badan menurut usia' },
                    height: { field: 'tinggi', z: 'zTB', unit: 'cm', label: 'Tinggi', title: 'Tinggi badan menurut usia' },
                    head: { field: 'kepala', z: 'zLK', unit: 'cm', label: 'Lingkar kepala', title: 'Lingkar kepala menurut usia' },
                    arm: { field: 'lengan', z: 'zLL', unit: 'cm', label: 'Lingkar lengan', title: 'Lingkar lengan menurut usia' }
                };
                var BLUE = '#0369a1', GREEN = '#bbf7d0', YELLOW = '#fde68a', RED = '#fecaca';
                Chart.defaults.font.family = "'Nunito', system-ui, sans-serif";
                Chart.defaults.font.size = 12;
                Chart.defaults.font.weight = 600;
                Chart.defaults.color = '#64748b';

                var labels = rows.map(function (r) { return r.usia_bulan; });
                var current = 'weight';

                // Pita WHO: merah (di luar ±3 SD), kuning (±2 s.d. ±3 SD), hijau (di antara ±2 SD).
                function bands(key) {
                    var b = ref[key];
                    var top = b.high3.map(function (v) { return v === null ? null : v + 1000; });
                    function band(data, fill, color) { return { data: data, borderWidth: 0, pointRadius: 0, tension: .35, spanGaps: true, fill: fill, backgroundColor: color, order: 5 }; }
                    return [
                        band(b.low3, 'start', RED),
                        band(b.low, '-1', YELLOW),
                        band(b.high, '-1', GREEN),
                        band(b.high3, '-1', YELLOW),
                        band(top, '-1', RED),
                        { data: b.median, borderColor: 'rgba(21,128,61,.75)', borderDash: [5, 4], borderWidth: 1.5, pointRadius: 0, tension: .35, spanGaps: true, fill: false, order: 4 }
                    ];
                }

                function child(key) {
                    var m = meta[key];
                    return {
                        label: m.label,
                        data: rows.map(function (r) { return r[m.field] === undefined ? null : r[m.field]; }),
                        borderColor: BLUE, backgroundColor: BLUE, borderWidth: 3, tension: .3, spanGaps: true, fill: false, order: 1,
                        pointBackgroundColor: '#fff', pointBorderColor: BLUE, pointBorderWidth: 3,
                        pointRadius: function (c) { return rows[c.dataIndex].hasTooltip ? 5 : 0; },
                        pointHoverRadius: function (c) { return rows[c.dataIndex].hasTooltip ? 7 : 0; }
                    };
                }

                function yRange(key) {
                    // Batasi sumbu Y ke sekitar rentang -3..+3 SD agar pita tidak memadatkan data.
                    var b = ref[key];
                    var lo = Math.min.apply(null, b.low3.filter(function (v) { return v !== null; }));
                    var hi = Math.max.apply(null, b.high3.filter(function (v) { return v !== null; }));
                    return { min: Math.floor(lo * 0.97), max: Math.ceil(hi * 1.03) };
                }

                var chart = new Chart(document.getElementById('growthChart'), {
                    type: 'line',
                    data: { labels: labels, datasets: bands('weight').concat([child('weight')]) },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'nearest', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a', padding: 10, cornerRadius: 10,
                                filter: function (item) { return item.datasetIndex === 6 && rows[item.dataIndex].hasTooltip; },
                                callbacks: {
                                    title: function (items) { return 'Usia ' + rows[items[0].dataIndex].usia; },
                                    label: function (item) {
                                        var m = meta[current], r = rows[item.dataIndex];
                                        return [m.label + ': ' + (r[m.field] ?? '-') + ' ' + m.unit, 'Z-score: ' + (r[m.z] ?? '-')];
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Usia (bulan)' }, grid: { display: false }, border: { display: false } },
                            y: Object.assign({ grid: { color: 'rgba(15,23,42,.06)' }, border: { display: false } }, yRange('weight'))
                        }
                    }
                });

                function select(key) {
                    current = key;
                    var m = meta[key];
                    chart.data.datasets = bands(key).concat([child(key)]);
                    var r = yRange(key);
                    chart.options.scales.y.min = r.min; chart.options.scales.y.max = r.max;
                    chart.update();
                    document.getElementById('chartTitle').textContent = m.title;
                    document.getElementById('chartUnit').textContent = m.unit;
                    var last = rows.filter(function (x) { return x[m.field] !== null && x[m.field] !== undefined; }).pop();
                    document.getElementById('chartValue').textContent = last ? last[m.field] : '-';
                    document.querySelectorAll('.m-card').forEach(function (b) { b.classList.toggle('is-active', b.dataset.key === key); });
                    document.querySelectorAll('.growth-info').forEach(function (p) { p.hidden = p.dataset.key !== key; });
                }
                document.querySelectorAll('.m-card').forEach(function (b) {
                    b.addEventListener('click', function () { select(b.dataset.key); });
                });
            })();
        </script>
    @endif
@endpush
