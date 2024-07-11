<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    public function index()
    {
        // return Payroll::with('employee')->get();
        return Payroll::all();
    }

    public function store(Request $request)
    {
        try {
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'payPeriodID' => 'required|string|exists:pay_periods,payPeriodID',
                'employeeID' => 'required|string',
                'totalEarnings' => 'required|numeric',
                'totalDeductions' => 'required|numeric',
                'netpay' => 'required|numeric',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            } else {
                return Payroll::create($validator->validated());
            }
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function show($id)
    {
        $payroll = Payroll::where('id', $id)->first();
        if (!$payroll) {
            return response()->json(['message' => 'not found'], 404);
        }
        return $payroll;
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
        try {
            $payroll = Payroll::where('id', $id)->first();
            if (!$payroll) {
                return response()->json(['message' => 'not found'], 404);
            }
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'payPeriodID' => 'required|string|exists:pay_periods,payPeriodID',
                'employeeID' => 'required|string',
                'totalEarnings' => 'required|numeric',
                'totalDeductions' => 'required|numeric',
                'netpay' => 'required|numeric',
            ]);


            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            } else {
                $payroll->update($validator->validated());

                return $payroll;
            }
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
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
