<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Earning extends Model
{
    use HasFactory;

    protected $primaryKey = 'earningID';
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
            $model->earningID = Str::uuid(); // Generate UUID for the 'payPeriodID' attribute
        });
    }

    protected $fillable = [
        'earningID',
        'payPeriodID',
        'employeeID',
        'earningType',
        'amount',
    ];
    public function payPeriod()
    {
        return $this->belongsTo(PayPeriod::class, 'payPeriodID');
    }
   
}
