<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory;

    // protected $fillable = ['employeeID', 'payPeriodID', 'totalEarnings', 'totalDeductions', 'netPay'];
    protected $table = 'payroll';
    protected $fillable = [
        'employeeID',
        'payPeriodID',
        'totalEarnings',
        'totalDeductions',
        'netpay',
    ];
    public function payPeriod()
    {
        return $this->belongsTo(PayPeriod::class, 'payPeriodID');
        
    }
}
