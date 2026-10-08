<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['parent_id', 'parent_phone', 'registered_by', 'name', 'nik', 'gender', 'bod', 'village_id'];

    protected $casts = ['bod' => 'date'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /** Kader/nakes yang ditugaskan pada anak ini. */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'child_user')->withPivot('role')->withTimestamps();
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(UserMeasurement::class);
    }

    public function latestMeasurement(): HasOne
    {
        return $this->hasOne(UserMeasurement::class)->latestOfMany('measured_at');
    }

    public function kpspResults(): HasMany
    {
        return $this->hasMany(KpspResult::class);
    }

    public function latestKpspResults(): HasMany
    {
        return $this->hasMany(KpspResult::class)->whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')->from('kpsp_results')->groupBy('child_id', 'age_category_id');
        });
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(RiskAssessment::class);
    }

    public function latestAssessment(): HasOne
    {
        return $this->hasOne(RiskAssessment::class)->latestOfMany('computed_at');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class)->latest('id');
    }

    public function openFollowUps(): HasMany
    {
        return $this->hasMany(FollowUp::class)->active();
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function ageInMonths(?CarbonInterface $at = null): int
    {
        return max(0, (int) $this->bod->diffInMonths($at ?? now()));
    }

    /**
     * Anak yang boleh dilihat pengguna: admin semua, orang tua anaknya sendiri,
     * kader/nakes yang ditugaskan atau yang mendaftarkan.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('children.parent_id', $user->id);

            if ($user->isStaff()) {
                $q->orWhere('children.registered_by', $user->id)
                    ->orWhereHas('staff', fn(Builder $s) => $s->where('users.id', $user->id));
            }
        });
    }
}
