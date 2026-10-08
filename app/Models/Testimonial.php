<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/Testimonial.php
class Testimonial extends Model
{
    protected $fillable = ['user_id', 'rating', 'message', 'is_public'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
