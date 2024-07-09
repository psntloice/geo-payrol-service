<?php

namespace Database\Factories;

use App\Models\Payroll;
use App\Models\PayPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;

class PayrollFactory extends Factory
{
    protected $model = Payroll::class;
/**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Fetch employee IDs from an external service
        // $employeeIDs = $this->getEmployeeIDsFromExternalService();

        $totalEarnings = $this->faker->randomFloat(2, 1000, 5000);
        $totalDeductions = $this->faker->randomFloat(2, 100, 1000);
        $netpay = $totalEarnings - $totalDeductions;

        return [
            'employeeID' => (string)$this->faker->unique()->numberBetween(1, 100),

            // 'employeeID' => $this->faker->randomElement($employeeIDs),
            'payPeriodID' => PayPeriod::inRandomOrder()->first()->payPeriodID,
            'totalEarnings' => $totalEarnings,
            'totalDeductions' => $totalDeductions,
            'netpay' => $netpay,
        ];
    }

    // private function getEmployeeIDsFromExternalService()
    // {
    //     // Example API call to get employee IDs
    //     $response = Http::get('https://external-service.com/api/employees');
    //     $employees = $response->json();
    //     return array_column($employees, 'id'); // Adjust based on your API response structure
    // }
}
