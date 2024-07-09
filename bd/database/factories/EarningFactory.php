<?php

namespace Database\Factories;

use App\Models\Earning;
use App\Models\Employee;
use App\Models\PayPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class EarningFactory extends Factory
{
    protected $model = Earning::class;
/**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'earningID' => $this->faker->unique()->numberBetween(1, 100),

            'employeeID' => (string)$this->faker->numberBetween(1, 100),
            'payPeriodID' => PayPeriod::inRandomOrder()->first()->payPeriodID,
            'earningType' => $this->faker->randomElement(['Salary', 'Bonus', 'Commission']),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    
}
