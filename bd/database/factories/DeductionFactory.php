<?php

namespace Database\Factories;

use App\Models\Deduction;
use App\Models\PayPeriod;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;

class DeductionFactory extends Factory
{
    protected $model = Deduction::class;
/**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
     

        return [
            'deductionID' => $this->faker->unique()->numberBetween(1, 100),
            'employeeID' => (string)$this->faker->numberBetween(1, 100),
            'payPeriodID' => PayPeriod::inRandomOrder()->first()->payPeriodID,
            'deductionType' => $this->faker->randomElement(['Tax', 'Insurance', 'Retirement']),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'created_at' => now(),
            'updated_at' => now(),
            // 'taxID' => Tax::inRandomOrder()->first()->taxID,
        ];
    }


}
