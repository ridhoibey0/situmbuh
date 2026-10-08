<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AgeCategory;
use App\Models\QuestionCategory;

class KpspQuestion extends Model
{
    protected $table = 'kpsp_questions';

    protected $fillable = [
        'age_category_id', 'question', 'image', 'description', 'category_id'
    ];

    public function ageCategory(){
        return $this->belongsTo(AgeCategory::class, 'age_category_id', 'id');
    }

    public function category(){
        return $this->belongsTo(QuestionCategory::class,'category_id', 'id');
    }
}