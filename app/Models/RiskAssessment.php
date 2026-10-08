<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskAssessment extends Model
{
    protected $fillable = ['child_id', 'measurement_id', 'score', 'level', 'factors', 'z_tb', 'z_bb', 'computed_at'];

    protected $casts = [
        'factors' => 'array',
        'computed_at' => 'datetime',
        'z_tb' => 'float',
        'z_bb' => 'float',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(UserMeasurement::class, 'measurement_id');
    }
}
