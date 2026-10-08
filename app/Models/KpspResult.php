<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AgeCategory;

class KpspResult extends Model
{
    protected $table = 'kpsp_results';

    protected $fillable = [
        'user_id', 'child_id', 'age_category_id', 'yes_count', 'interpretation', 'intervensi'
    ];

    public function ageCategory(){
        return $this->belongsTo(AgeCategory::class, 'age_category_id', 'id');
    }

    public function categoryQuestion() {
        return $this->belongsTo(QuestionCategory::class, "age_category_id", "id");
    }

    public function child(){
        return $this->belongsTo(Child::class);
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}