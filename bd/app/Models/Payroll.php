<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Payroll extends Model
{
    use HasFactory;

    // protected $fillable = ['employeeID', 'payPeriodID', 'totalEarnings', 'totalDeductions', 'netPay'];
    protected $table = 'payroll';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::uuid(); // Generate UUID for the 'payPeriodID' attribute
        });
    }

    protected $fillable = [
        'id',
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
