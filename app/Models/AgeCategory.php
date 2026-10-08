<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\KpspQuestion;


class AgeCategory extends Model
{
    protected $table = 'age_categories';

    protected $fillable = [
        'name', 'min_age', 'max_age'
    ];


    public function questions()
    {
        return $this->hasMany(KpspQuestion::class, 'age_category_id', 'id');
    }
}