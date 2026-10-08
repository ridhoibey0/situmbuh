@props(['assessment' => null])
<div {{ $attributes }}>
    @if (!$assessment)
        <p class="mb-0" style="color: var(--muted)">Belum ada penilaian prioritas.</p>
    @else
        <div class="mb-2">
            <x-risk-badge :level="$assessment->level" />
            <div style="font-size: 13px; color: var(--muted)">
                Skor {{ $assessment->score }}/100 &middot; dihitung {{ $assessment->computed_at->translatedFormat('d M Y H:i') }}
            </div>
        </div>
        @forelse ($assessment->factors as $factor)
            <div class="u-factor">
                <div class="row1">
                    <span class="t">{{ $factor['label'] }}</span>
                    <span class="pts">+{{ $factor['points'] }}</span>
                </div>
                <div class="d">{{ $factor['detail'] }}</div>
            </div>
        @empty
            <p class="mb-2" style="color: var(--muted)">Tidak ada faktor yang perlu diperhatikan saat ini.</p>
        @endforelse
        <p class="mb-0" style="font-size: 12.5px; color: var(--muted)">
            Skor adalah alat bantu menentukan urutan pemantauan, bukan diagnosis. Keputusan tetap pada tenaga kesehatan.
        </p>
    @endif
</div>
