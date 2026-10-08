<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\KpspQuestion;


class UserMeasurement extends Model
{
    protected $table = 'user_measurements';

    protected $fillable = [
        'user_id',
        'child_id',
        'measured_by',
        'weight',
        'height',
        'head_circumference',
        'arm_circumference',
        'measured_at'
    ];

    protected $casts = ['measured_at' => 'datetime'];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
