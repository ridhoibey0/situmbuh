<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ChatMessage;


class ChatSession extends Model
{
    protected $table = 'chat_sessions';

    protected $fillable = ['user_id'];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
