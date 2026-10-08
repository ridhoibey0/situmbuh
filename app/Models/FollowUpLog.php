<?php

namespace App\Models;

use App\Enums\FollowUpStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUpLog extends Model
{
    protected $fillable = ['follow_up_id', 'user_id', 'from_status', 'to_status', 'note'];

    protected $casts = [
        'from_status' => FollowUpStatus::class,
        'to_status' => FollowUpStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
