@php
    /** @var \App\Models\FollowUp $followUp */
    $canUpdate = auth()->user()->can('update', $followUp);
    $showChild = $showChild ?? false;
    $overdue = $followUp->isOverdue();
    $statusColor = match ($followUp->status->value) {
        'done' => 'var(--low)', 'cancelled' => 'var(--muted)', 'in_progress' => 'var(--accent-ink)', default => 'var(--ink-soft)',
    };
@endphp
<div class="u-panel fu-card" style="margin-top: 10px">
    <div class="d-flex justify-content-between align-items-start gap-2">
        <div>
            @if ($showChild)
                <a href="{{ route('kader.children.show', $followUp->child_id) }}" class="fw-bold text-decoration-none" style="color: var(--ink)">{{ $followUp->child->name }}</a><br>
            @endif
            <span class="fw-semibold" style="color: var(--ink)">{{ $followUp->action_type->label() }}</span>
        </div>
        <span class="u-small fw-semibold text-nowrap" style="color: {{ $statusColor }}">{{ $followUp->status->label() }}</span>
    </div>
    <div class="u-small" style="{{ $overdue ? 'color: var(--high); font-weight: 600' : 'color: var(--muted)' }}">
        Tenggat {{ $followUp->due_date->translatedFormat('d M Y') }}{{ $overdue ? ' (terlewat)' : '' }}
        &middot; PJ: {{ $followUp->assignee?->name ?? '-' }}
    </div>
    @if ($followUp->notes)
        <div class="u-small mt-1">{{ $followUp->notes }}</div>
    @endif

    @if ($followUp->status === \App\Enums\FollowUpStatus::Done)
        <div class="mt-2 p-2" style="background: var(--bg); border-radius: 8px">
            <div class="u-small fw-semibold">Selesai {{ $followUp->completed_at->translatedFormat('d M Y') }}</div>
            @if ($followUp->result_notes)
                <div class="u-small">{{ $followUp->result_notes }}</div>
            @endif
            @if ($followUp->baseline && $followUp->outcome)
                <div class="u-small mt-1">
                    Sebelum: <x-risk-badge :level="$followUp->baseline->level" /> (skor {{ $followUp->baseline->score }})<br>
                    Sesudah: <x-risk-badge :level="$followUp->outcome->level" /> (skor {{ $followUp->outcome->score }})
                    &middot; <strong>{{ $followUp->outcomeLabel() }}</strong>
                </div>
                <div class="u-small u-muted">
                    TB/U {{ $followUp->baseline->z_tb ?? '-' }} &rarr; {{ $followUp->outcome->z_tb ?? '-' }} SD &middot;
                    BB/U {{ $followUp->baseline->z_bb ?? '-' }} &rarr; {{ $followUp->outcome->z_bb ?? '-' }} SD
                </div>
            @else
                <div class="u-small u-muted">Menunggu pengukuran berikutnya untuk evaluasi hasil.</div>
            @endif
        </div>
    @endif

    @if ($canUpdate && $followUp->status->isActive())
        <form method="POST" action="{{ route('kader.follow-ups.update', $followUp) }}" class="mt-2">
            @csrf
            @method('PATCH')
            <textarea name="result_notes" class="form-control mb-2" rows="2" placeholder="Catatan hasil (diisi saat menyelesaikan)"></textarea>
            <div class="d-flex gap-2">
                @if ($followUp->status === \App\Enums\FollowUpStatus::Open)
                    <button name="status" value="in_progress" class="btn btn-outline-primary btn-sm">Mulai</button>
                @endif
                <button name="status" value="done" class="btn btn-primary btn-sm">Selesai</button>
                <button name="status" value="cancelled" class="btn btn-outline-secondary btn-sm ms-auto"
                    onclick="return confirm('Batalkan tindak lanjut ini?')">Batalkan</button>
            </div>
        </form>
    @endif
</div>
