<?php

namespace Database\Factories;

use App\Models\PayPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayPeriodFactory extends Factory
{
     /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   
   
    protected $model = PayPeriod::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('-1 years', 'now');
        $end = (clone $start)->modify('+15 days');
        $paydate = (clone $end)->modify('+1 day');
        
        return [
            'payPeriodID' => $this->faker->unique()->numberBetween(1, 10),    
            'disbursmentDate' => $paydate->format('Y-m-d'),
        ];
    }
}
