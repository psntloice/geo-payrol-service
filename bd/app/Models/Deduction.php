<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Deduction extends Model
{
    use HasFactory;

    protected $primaryKey = 'deductionID';
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
            $model->deductionID = Str::uuid(); // Generate UUID for the 'payPeriodID' attribute
        });
    }


    protected $fillable = [
        'deductionID',
        'payPeriodID',
        'employeeID',
        'deductionType',
        'amount',
    ];
    public function payPeriod()
    {
        return $this->belongsTo(PayPeriod::class, 'payPeriodID');
    }

    // public function tax()
    // {
    //     return $this->belongsTo(Tax::class, 'taxID');
    // }
}
