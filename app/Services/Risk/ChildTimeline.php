<?php

namespace App\Services\Risk;

use App\Models\Child;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Riwayat aktivitas satu anak dalam urutan terbaru lebih dulu: pengukuran, perubahan level prioritas,
 * skrining KPSP, dan perubahan status tindak lanjut.
 */
class ChildTimeline
{
    /** @return Collection<int, array{at: CarbonInterface, kind: string, title: string, detail: ?string}> */
    public function for(Child $child, int $limit = 15): Collection
    {
        $events = collect();

        foreach ($child->measurements as $m) {
            $parts = array_filter([
                $m->weight !== null ? 'BB ' . rtrim(rtrim(number_format((float) $m->weight, 2, ',', ''), '0'), ',') . ' kg' : null,
                $m->height !== null ? 'TB ' . rtrim(rtrim(number_format((float) $m->height, 2, ',', ''), '0'), ',') . ' cm' : null,
            ]);
            $events->push($this->event($m->measured_at ?? $m->created_at, 'pengukuran', 'Pengukuran dicatat', implode(', ', $parts)));
        }

        $previous = null;
        foreach ($child->assessments()->orderBy('computed_at')->orderBy('id')->get() as $a) {
            if ($previous === null || $previous->level !== $a->level) {
                $title = $previous === null
                    ? 'Penilaian prioritas pertama: ' . ucfirst($a->level)
                    : 'Prioritas berubah dari ' . $previous->level . ' ke ' . $a->level;
                $events->push($this->event($a->computed_at, 'prioritas', $title, 'Skor ' . $a->score));
            }
            $previous = $a;
        }

        foreach ($child->kpspResults as $r) {
            $events->push($this->event($r->created_at, 'kpsp', 'Skrining KPSP', $r->interpretation));
        }

        foreach ($child->followUps()->with('logs')->get() as $followUp) {
            foreach ($followUp->logs as $log) {
                $events->push($this->event(
                    $log->created_at,
                    'tindak-lanjut',
                    $followUp->action_type->label() . ': ' . strtolower($log->to_status->label()),
                    $log->note,
                ));
            }
        }

        return $events->sortByDesc(fn($e) => $e['at']->getTimestamp())->take($limit)->values();
    }

    private function event(CarbonInterface $at, string $kind, string $title, ?string $detail): array
    {
        return ['at' => $at, 'kind' => $kind, 'title' => $title, 'detail' => $detail ?: null];
    }
}
