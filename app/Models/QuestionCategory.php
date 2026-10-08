<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\KpspQuestion;


class QuestionCategory extends Model
{
    protected $table = 'question_categories';

    protected $fillable = [
        'name'
    ];


    public function questions() 
    {
        return $this->hasMany(KpspQuestion::class, 'age_category_id', 'id');
    }
}