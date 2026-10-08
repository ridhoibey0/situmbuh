<?php

namespace App\Services\Admin;

use App\Enums\FollowUpStatus;
use App\Models\Child;
use App\Models\FollowUp;
use App\Models\RiskAssessment;
use App\Models\UserMeasurement;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Data agregat untuk pengelola layanan: distribusi prioritas, penyelesaian tindak lanjut,
 * hasil evaluasi, dan pola pemantauan. Tidak memuat data identitas selain daftar prioritas teratas.
 */
class DashboardMetrics
{
    private CarbonInterface $now;

    public function __construct(?CarbonInterface $now = null)
    {
        $this->now = $now ?? now();
    }

    public function summary(int $months = 6): array
    {
        $totalChildren = Child::count();
        $levels = $this->levelCounts();
        $assessed = array_sum($levels);

        return [
            'total_children' => $totalChildren,
            'levels' => $levels + ['belum' => max(0, $totalChildren - $assessed)],
            'missed' => $this->missedCount(),
            'followups' => $this->followUps(),
            'outcomes' => $this->outcomes(),
            'monthly' => $this->monthly($months),
            'top' => $this->topPriority(8),
        ];
    }

    private function latestIds()
    {
        return RiskAssessment::query()->selectRaw('MAX(id)')->groupBy('child_id');
    }

    /** @return array{tinggi: int, sedang: int, rendah: int} */
    private function levelCounts(): array
    {
        $rows = RiskAssessment::whereIn('id', $this->latestIds())
            ->selectRaw('level, COUNT(*) AS total')->groupBy('level')->pluck('total', 'level');

        return [
            'tinggi' => (int) ($rows['tinggi'] ?? 0),
            'sedang' => (int) ($rows['sedang'] ?? 0),
            'rendah' => (int) ($rows['rendah'] ?? 0),
        ];
    }

    private function missedCount(): int
    {
        return RiskAssessment::whereIn('id', $this->latestIds())
            ->where(fn($w) => $w->whereJsonContains('factors', ['code' => 'monitoring_overdue'])
                ->orWhereJsonContains('factors', ['code' => 'monitoring_long_overdue']))
            ->count();
    }

    private function followUps(): array
    {
        $byStatus = FollowUp::query()->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $count = fn(FollowUpStatus $s) => (int) ($byStatus[$s->value] ?? 0);

        $done = $count(FollowUpStatus::Done);
        $total = (int) $byStatus->sum();
        $onTime = FollowUp::where('status', FollowUpStatus::Done->value)
            ->whereRaw('DATE(completed_at) <= due_date')->count();

        return [
            'total' => $total,
            'active' => $count(FollowUpStatus::Open) + $count(FollowUpStatus::InProgress),
            'done' => $done,
            'cancelled' => $count(FollowUpStatus::Cancelled),
            'overdue' => FollowUp::active()->whereDate('due_date', '<', $this->now->toDateString())->count(),
            'completion_rate' => $total > 0 ? (int) round($done / $total * 100) : null,
            'on_time_rate' => $done > 0 ? (int) round($onTime / $done * 100) : null,
        ];
    }

    /** Hasil evaluasi: perubahan skor prioritas dari baseline ke pemantauan setelah tindak lanjut selesai. */
    private function outcomes(): array
    {
        $row = DB::table('follow_ups as f')
            ->join('risk_assessments as b', 'b.id', '=', 'f.baseline_assessment_id')
            ->join('risk_assessments as o', 'o.id', '=', 'f.outcome_assessment_id')
            ->selectRaw('SUM(o.score < b.score) AS improved, SUM(o.score = b.score) AS same_score, SUM(o.score > b.score) AS worse')
            ->first();

        return [
            'membaik' => (int) ($row->improved ?? 0),
            'tetap' => (int) ($row->same_score ?? 0),
            'memburuk' => (int) ($row->worse ?? 0),
        ];
    }

    /** Pengukuran per bulan serta tindak lanjut dibuat dan selesai. */
    private function monthly(int $months): array
    {
        $start = $this->now->copy()->startOfMonth()->subMonths($months - 1);

        $keys = [];
        for ($i = 0; $i < $months; $i++) {
            $keys[] = $start->copy()->addMonths($i);
        }

        $group = fn($dates) => collect($dates)->filter()->countBy(fn($d) => $d->format('Y-m'));

        $measurements = $group(UserMeasurement::whereDate('measured_at', '>=', $start)->pluck('measured_at'));
        $created = $group(FollowUp::whereDate('created_at', '>=', $start)->pluck('created_at'));
        $done = $group(FollowUp::where('status', FollowUpStatus::Done->value)->whereDate('completed_at', '>=', $start)->pluck('completed_at'));

        return [
            'labels' => array_map(fn($d) => $d->translatedFormat('M Y'), $keys),
            'measurements' => array_map(fn($d) => (int) ($measurements[$d->format('Y-m')] ?? 0), $keys),
            'created' => array_map(fn($d) => (int) ($created[$d->format('Y-m')] ?? 0), $keys),
            'done' => array_map(fn($d) => (int) ($done[$d->format('Y-m')] ?? 0), $keys),
        ];
    }

    private function topPriority(int $limit)
    {
        return Child::query()
            ->whereHas('latestAssessment', fn($a) => $a->where('score', '>', 0))
            ->with('latestAssessment', 'latestMeasurement', 'parent:id,parent_name')
            ->get()
            ->sortByDesc(fn($c) => $c->latestAssessment->score)
            ->take($limit)
            ->values();
    }
}
