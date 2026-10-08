<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpspAnswer extends Model
{
    protected $table = 'kpsp_answers';

    protected $fillable = ['kpsp_result_id', 'question_id', 'answer'];

    public function question(){
       return $this->belongsTo(KpspQuestion::class,'question_id', 'id');
    }

    public function result(): BelongsTo {
        return $this->belongsTo(KpspResult::class,'kpsp_result_id', 'id');
    }
}
