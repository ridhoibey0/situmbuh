<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhoGrowthStandard extends Model
{
    protected $fillable = ['gender', 'parameter', 'age_in_months', 'sd_3_negatif', 'sd_2_negatif', 'sd_1_negatif', 'sd_median', 'sd_1_positif', 'sd_2_positif', 'sd_3_positif'];
}
