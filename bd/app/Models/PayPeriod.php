<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class PayPeriod extends Model
{
    use HasFactory;
    protected $primaryKey = 'payPeriodID'; // Specify the custom primary key name
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
            $model->payPeriodID = Str::uuid(); // Generate UUID for the 'payPeriodID' attribute
        });
    }

    protected $fillable = [
        'payPeriodID',
        'disbursmentDate',
    ];
    protected $dates = ['disbursmentDate'];
    // If you need timestamps, keep this line
    public $timestamps = true;
    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'payPeriodID');
    }
}
