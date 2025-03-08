<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fore_Closure extends Model
{
    use HasFactory;

    protected $fillable=[
        'EMI_ID',
        'ClosureAmount',
        'ChargesPercent',
        'ClosureCharges',
        'ClosureDate'
    ];
}
