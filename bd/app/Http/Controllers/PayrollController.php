<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PayrollController extends Controller
{
    public function index()
    {
        // return Payroll::with('employee')->get();
       return Payroll::all();

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
            'employeeID' => 'required|integer',       
                 'amount' => 'required|numeric',
            'totalEarnings' => 'required|numeric',
            'totalDeductions' => 'required|numeric',
            'netpay' => 'required|numeric',

        ]);

        return Payroll::create($validated);
    }

    public function show($id)
    {
        $payroll = Payroll::where('id', $id)->first();
        if (!$payroll) {
            return response()->json(['message' => 'not found'], 404);
        }
        return $payroll;
        // return $payroll->load('employee');
    }
    public function showPayrollSpecificEmployee(Payroll $payroll)
    {
        // $earning = Earning::where('id', $id)->first();
        // if ($earning) {
        //     return response()->json(['message' => 'not found'], 404);
        // }
        // return $earning;
        // return $payroll->load('employee');
    }
    // public function update(Request $request, Payroll $payroll)


    public function update(Request $request, $id)
    {
       
        // $payroll = Payroll::where('id', $id)->first();
        // // if (!$payroll) {
        // //     return response()->json(['message' => 'not found'], 404);
        // // }
        // Log::info('Validated data:', ['payPeriodID' => 23]);

        // $validated = $request->validate([
        //     'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
        //     'employeeID' => 'required|integer',       
        //          'amount' => 'required|numeric',
        //     'totalEarnings' => 'required|numeric',
        //     'totalDeductions' => 'required|numeric',
        //     'netpay' => 'required|numeric',

        // ]);


        // Log::info('Validated data:', "kkf");

        // // $payroll->update($validated);

        // return $validated;

        try {
            // Fetch the payroll record by ID
        $payroll = Payroll::where('id', $id)->first();
    
            // Validate the request data
            $validated = $request->validate([
                'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
                'employeeID' => 'required|integer',
                'amount' => 'required|numeric',
                'totalEarnings' => 'required|numeric',
                'totalDeductions' => 'required|numeric',
                'netpay' => 'required|numeric',
            ]);
    
            // If validation passes, update the payroll record
            $payroll->update($validated);
    
            // Optionally, return a success response or do further processing
            return response()->json(['message' => 'Payroll updated successfully', 'payroll' => $payroll], 200);
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation error while updating payroll', ['errors' => $e->errors()]);
    
            // Optionally, return a response with validation errors
            return response()->json(['error' => $e->errors()], 422); // HTTP status code 422 for validation errors
        } catch (\Exception $e) {
            // Log other unexpected errors
            Log::error('Error updating payroll', ['exception' => $e->getMessage()]);
    
            // Return a generic error response
            return response()->json(['error' => 'Failed to update payroll'], 500); // HTTP status code 500 for server errors
        }
    }
    // public function destroy(Payroll $payroll)

    public function destroy($id)
    {
        $payroll = Payroll::where('id', $id)->first();
        if (!$payroll) {
            return response()->json(['message' => 'not found'], 404);
        }
        $payroll->delete();
        return response()->json(['message' => 'deleted successfully']);;
    }
}
