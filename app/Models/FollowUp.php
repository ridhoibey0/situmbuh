<?php

namespace App\Models;

use App\Enums\FollowUpAction;
use App\Enums\FollowUpStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FollowUp extends Model
{
    protected $fillable = [
        'child_id', 'baseline_assessment_id', 'outcome_assessment_id', 'assigned_to', 'created_by',
        'action_type', 'notes', 'due_date', 'status', 'completed_at', 'result_notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'status' => FollowUpStatus::class,
        'action_type' => FollowUpAction::class,
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function baseline(): BelongsTo
    {
        return $this->belongsTo(RiskAssessment::class, 'baseline_assessment_id');
    }

    public function outcome(): BelongsTo
    {
        return $this->belongsTo(RiskAssessment::class, 'outcome_assessment_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FollowUpLog::class)->latest('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', array_map(fn($s) => $s->value, FollowUpStatus::active()));
    }

    public function isOverdue(): bool
    {
        return $this->status->isActive() && $this->due_date->lt(now()->startOfDay());
    }

    /** Selisih skor prioritas (outcome - baseline). Negatif berarti membaik. Null bila belum bisa dievaluasi. */
    public function scoreChange(): ?int
    {
        return $this->baseline && $this->outcome ? $this->outcome->score - $this->baseline->score : null;
    }

    public function outcomeLabel(): ?string
    {
        $change = $this->scoreChange();

        return match (true) {
            $change === null => null,
            $change < 0 => 'Membaik',
            $change > 0 => 'Memburuk',
            default => 'Tetap',
        };
    }
}
