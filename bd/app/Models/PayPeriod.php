<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayPeriod extends Model
{
    use HasFactory;
    protected $primaryKey = 'payPeriodID'; // Specify the custom primary key name

    protected $fillable = [
        'disbursmentDate',
    ];

    // If you need timestamps, keep this line
    public $timestamps = true;
    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'payPeriodID');
    }
}
