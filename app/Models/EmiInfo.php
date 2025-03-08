<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmiInfo extends Model
{
    use HasFactory;
    protected $fillable=[
        'PlotPrincipalAmount',
        'EmiDate',
        'LoanTenure',
        'InterestRate', 
        
        // for float or variable emi
        // 'Base', 
        // 'Rate/Benchmark' 'Rate',
        // 'Spread or margin',

       
        // 'Compounding Frequency',  // for Compound Interest Rate

        'EmiAmount', // monthly emi amount
        'InterestRateAmount', // interest rate amount
        'MonthInstallment', // total monthly payment (monthly emi amount + interest rate amount)
        'TotalInterestAmount', // total amount of interest amount (loantenure * montgInstallment)
        'payment_plan_type'
    ];
}
