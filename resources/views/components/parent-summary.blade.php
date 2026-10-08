@props(['assessment' => null, 'followUps' => collect()])
@php
    $messages = [
        'tinggi' => ['is-tinggi', 'Perlu perhatian segera', 'Sebaiknya segera konsultasikan ke kader atau tenaga kesehatan.'],
        'sedang' => ['is-sedang', 'Perlu dipantau', 'Pantau perkembangan dan ikuti jadwal pengukuran berikutnya.'],
        'rendah' => ['is-rendah', 'Kondisi baik', 'Tetap lakukan pengukuran rutin setiap bulan.'],
    ];
    [$cls, $title, $advice] = $messages[$assessment?->level] ?? ['is-belum', 'Belum ada penilaian', 'Lakukan pengukuran untuk melihat kondisi anak.'];
@endphp
<div {{ $attributes->merge(['class' => 'u-panel']) }}>
    <span class="lvl {{ $cls }}" style="font-size: 15px">{{ $title }}</span>
    <p class="mb-0 mt-1 u-small" style="color: var(--ink-soft)">{{ $advice }}</p>

    @if ($assessment && count($assessment->factors))
        <hr class="u-divider">
        <div class="u-small fw-semibold" style="color: var(--ink)">Hal yang perlu diperhatikan</div>
        <ul class="u-small mb-0 mt-1 ps-3">
            @foreach ($assessment->factors as $factor)
                <li>{{ $factor['label'] }}</li>
            @endforeach
        </ul>
    @endif

    @if ($followUps->isNotEmpty())
        <hr class="u-divider">
        <div class="u-small fw-semibold" style="color: var(--ink)">Tindak lanjut yang sedang berjalan</div>
        @foreach ($followUps as $followUp)
            <div class="u-small mt-1">
                {{ $followUp->action_type->label() }}
                <span class="u-muted">&middot; {{ $followUp->status->label() }}</span><br>
                <span class="{{ $followUp->isOverdue() ? '' : 'u-muted' }}" style="{{ $followUp->isOverdue() ? 'color: var(--high)' : '' }}">
                    Tenggat {{ $followUp->due_date->translatedFormat('d M Y') }}{{ $followUp->isOverdue() ? ' (terlewat)' : '' }}
                </span>
            </div>
        @endforeach
    @endif

    <p class="mt-3 mb-0 u-muted" style="font-size: 12px">
        Informasi ini membantu pemantauan dan bukan diagnosis. Hubungi tenaga kesehatan untuk penilaian medis.
    </p>
</div>
