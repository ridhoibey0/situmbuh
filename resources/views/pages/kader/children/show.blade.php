@extends('layouts.users')

@section('title', $child->name . ' - Situmbuh')

@php
    $fmt = fn($v) => $v !== null ? rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',') : '-';
@endphp

@section('content')
    <div class="u-head">
        <a href="{{ route('kader.children.index') }}" class="u-back">&larr; Daftar anak</a>
        <h1 class="u-h1">{{ $child->name }}</h1>
        <p class="u-lead">
            {{ $child->gender === 'male' ? 'Laki-laki' : 'Perempuan' }} &middot;
            lahir {{ $child->bod->translatedFormat('d M Y') }} ({{ $child->ageInMonths() }} bulan)
        </p>
        @if ($child->parent)
            <p class="u-lead">Orang tua: {{ $child->parent->parent_name }} &middot; {{ $child->parent->phone }}</p>
        @elseif ($child->parent_phone)
            <p class="u-lead">Orang tua belum punya akun &middot; {{ $child->parent_phone }}</p>
        @endif
        @if ($whatsapp)
            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm mt-2">
                <i class="bi bi-whatsapp"></i> Hubungi orang tua
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="u-panel" aria-labelledby="h-why">
        <h2 class="u-panel-title" id="h-why">Mengapa prioritas ini?</h2>
        <x-risk-factors :assessment="$assessment" />
    </section>

    @if (count($chart['labels']) > 1)
        <section class="u-panel">
            <h2 class="u-panel-title">Grafik pertumbuhan</h2>
            <p class="u-small u-muted mb-2">Sumbu bawah: tanggal pengukuran</p>
            <div style="position: relative; height: 170px"><canvas id="chartWeight"></canvas></div>
            <div style="position: relative; height: 170px" class="mt-3"><canvas id="chartHeight"></canvas></div>
        </section>
    @endif

    <section class="u-panel">
        <h2 class="u-panel-title">Tindak lanjut</h2>
        @error('status')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        @forelse ($followUps as $followUp)
            @include('pages.kader.follow-ups._card', ['followUp' => $followUp])
        @empty
            <p class="u-muted mb-0">Belum ada tindak lanjut.</p>
        @endforelse

        @if ($canCreateFollowUp)
            <hr class="u-divider">
            <form method="POST" action="{{ route('kader.children.follow-ups.store', $child) }}">
                @csrf
                <div class="u-field">
                    <label for="action_type">Jenis tindak lanjut</label>
                    <select name="action_type" id="action_type" class="form-select" required>
                        @foreach (\App\Enums\FollowUpAction::cases() as $action)
                            <option value="{{ $action->value }}" @selected(old('action_type') === $action->value)>{{ $action->label() }}</option>
                        @endforeach
                    </select>
                    @error('action_type')<div class="u-error">{{ $message }}</div>@enderror
                </div>
                <div class="row g-2 u-field">
                    <div class="col-6">
                        <label for="due_date">Tenggat</label>
                        <input type="date" class="form-control" id="due_date" name="due_date"
                            value="{{ old('due_date', now()->addWeek()->toDateString()) }}" min="{{ now()->toDateString() }}" required>
                        @error('due_date')<div class="u-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label for="assigned_to">Penanggung jawab</label>
                        <select name="assigned_to" id="assigned_to" class="form-select">
                            @foreach ($assignees as $assignee)
                                <option value="{{ $assignee->id }}" @selected((int) old('assigned_to', auth()->id()) === $assignee->id)>{{ $assignee->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')<div class="u-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="u-field">
                    <label for="notes">Catatan</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" maxlength="1000"
                        placeholder="Alasan atau hal yang perlu dipantau">{{ old('notes', $assessment ? collect($assessment->factors)->pluck('label')->take(3)->implode('; ') : '') }}</textarea>
                    @error('notes')<div class="u-error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Buat tindak lanjut</button>
            </form>
        @endif
    </section>

    @if ($canRecord)
        <section class="u-panel">
            <h2 class="u-panel-title">Catat pengukuran</h2>
            <form method="POST" action="{{ route('kader.children.measurements.store', $child) }}">
                @csrf
                <div class="u-field">
                    <label for="measured_at">Tanggal</label>
                    <input type="date" class="form-control" id="measured_at" name="measured_at"
                        value="{{ old('measured_at', now()->toDateString()) }}" required>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label for="weight">BB (kg)</label>
                        <input type="number" step="0.1" inputmode="decimal" class="form-control" id="weight" name="weight" value="{{ old('weight') }}" required>
                    </div>
                    <div class="col-6">
                        <label for="height">TB/PB (cm)</label>
                        <input type="number" step="0.1" inputmode="decimal" class="form-control" id="height" name="height" value="{{ old('height') }}" required>
                    </div>
                    <div class="col-6">
                        <label for="head_circumference">LK (cm) <span class="u-muted fw-normal">opsional</span></label>
                        <input type="number" step="0.1" inputmode="decimal" class="form-control" id="head_circumference" name="head_circumference" value="{{ old('head_circumference') }}">
                    </div>
                    <div class="col-6">
                        <label for="arm_circumference">LiLA (cm) <span class="u-muted fw-normal">opsional</span></label>
                        <input type="number" step="0.1" inputmode="decimal" class="form-control" id="arm_circumference" name="arm_circumference" value="{{ old('arm_circumference') }}">
                    </div>
                </div>
                @if ($errors->any() && !$errors->has('status'))
                    <div class="u-error mt-2">{{ $errors->first() }}</div>
                @endif
                <button type="submit" class="btn btn-primary w-100 mt-3">Simpan pengukuran</button>
            </form>
        </section>
    @endif

    <section class="u-panel">
        <h2 class="u-panel-title">Riwayat pengukuran</h2>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Tanggal</th><th class="text-end">BB</th><th class="text-end">TB</th><th class="text-end">LK</th><th class="text-end">LiLA</th></tr></thead>
                <tbody>
                    @forelse ($measurements as $m)
                        <tr>
                            <td>{{ $m->measured_at?->translatedFormat('d M Y') }}</td>
                            <td class="text-end">{{ $fmt($m->weight) }}</td>
                            <td class="text-end">{{ $fmt($m->height) }}</td>
                            <td class="text-end">{{ $fmt($m->head_circumference) }}</td>
                            <td class="text-end">{{ $fmt($m->arm_circumference) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="u-muted">Belum ada pengukuran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="u-panel">
        <h2 class="u-panel-title">Riwayat aktivitas</h2>
        @if ($timeline->isEmpty())
            <p class="u-muted mb-0">Belum ada aktivitas.</p>
        @else
            <ul class="u-tl">
                @foreach ($timeline as $event)
                    <li>
                        <time>{{ $event['at']->translatedFormat('d M Y') }}</time>
                        <div>
                            <div class="t">{{ $event['title'] }}</div>
                            @if ($event['detail'])<div class="d">{{ $event['detail'] }}</div>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection

@if (count($chart['labels']) > 1)
    @push('addon-script')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                var d = @json($chart);
                Chart.defaults.font.family = "'Nunito', system-ui, sans-serif";
                Chart.defaults.font.size = 12;
                Chart.defaults.color = '#64748b';
                function line(id, label, data, color, unit) {
                    new Chart(document.getElementById(id), {
                        type: 'line',
                        data: { labels: d.labels, datasets: [{ label: label, data: data, borderColor: color, backgroundColor: color, pointRadius: 3, tension: 0.25, spanGaps: true }] },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { display: false }, title: { display: true, text: label + ' (' + unit + ')', align: 'start' } },
                            scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' } } }
                        }
                    });
                }
                line('chartWeight', 'Berat badan', d.weight, '#0f172a', 'kg');
                line('chartHeight', 'Tinggi badan', d.height, '#16a34a', 'cm');
            })();
        </script>
    @endpush
@endif
