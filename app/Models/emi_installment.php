<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class emi_installment extends Model
{
    use HasFactory;

    protected $fillable=[
        'EMI_ID',
        'EMI_Amount',
        'EMI_Date',
        'EMI_Status',
        'PaymentType',
        'TransactionID'
    ];
}
