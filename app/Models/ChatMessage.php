<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';
    protected $appends = ['created_at_human'];
    protected $fillable = ['chat_session_id', 'sender', 'message'];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
    public function getCreatedAtHumanAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }
}
