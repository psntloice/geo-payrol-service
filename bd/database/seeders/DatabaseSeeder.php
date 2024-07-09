<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Deduction;
use App\Models\Earning;
use App\Models\PayPeriod;
use App\Models\Payroll;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        PayPeriod::factory()->count(7)->create();

         // Seed Deductions
         Deduction::factory()->count(10)->create();

         // Seed Earnings
         Earning::factory()->count(10)->create();
 
         // Seed PayPeriods
 
         // Seed Payrolls
         Payroll::factory()->count(20)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
