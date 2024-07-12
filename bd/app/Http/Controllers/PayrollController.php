<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\PayPeriod;


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
            //pick date of payment
            $latestPayPeriod = PayPeriod::orderBy('disbursmentDate', 'desc')->first();

            //error if not found
            if ($latestPayPeriod === null || !isset($latestPayPeriod['disbursmentDate'])) {
                return "no dates found";
            }

            // Assuming $latestPayPeriod->disbursmentDate is in "Y-m-d" format
            $disbursmentDate = Carbon::parse($latestPayPeriod->disbursmentDate);

            // Extract the month
            $latestMonth = $disbursmentDate->month;
            $latestYear = $disbursmentDate->year;

            // Get the current date
            $currentDate = Carbon::now();
            $currentMonth = $currentDate->month;
            $currentYear = $currentDate->year;



            // Compare the months and years 
            if ($latestMonth !== $currentMonth || $latestYear !== $currentYear) {
                return response()->json(['error' => 'The latest pay period (' . $disbursmentDate->format('Y-m-d') . ') is not for the current month (' . $currentDate->format('Y-m-d') . '): ' . 'Please set the disbursement date',], 404);
            }

            // If comparison is successful, proceed to fetch the ID
            $latestPayPeriodId = $latestPayPeriod->payPeriodID;
            if ($latestPayPeriodId === null || !isset($latestPayPeriod['disbursmentDate'])) {
                return "no id for the date found";
            }

           // Validate incoming request
           $validator = Validator::make($request->all(), [
            'employeeID' => 'required|string',
            'totalEarnings' => 'required|numeric',
            'totalDeductions' => 'required|numeric',
            'netpay' => 'required|numeric',
        ]);


        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['payroll validation errors' => $errors], 400);
        }
        // Get the validated data
        $validatedData = $validator->validated();
        // Add additional data
        $additionalData = [
            'payPeriodID' => $latestPayPeriodId,
        ];

        // Combine validated data with additional data
        $dataToUpdate = array_merge($validatedData, $additionalData);
                return Payroll::create($dataToUpdate);
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
            //check if it first exists
            $payroll = Payroll::where('id', $id)->first();
            if (!$payroll) {
                return response()->json(['message' => 'payroll not found'], 404);
            }
            //pick date of payment
            $latestPayPeriod = PayPeriod::orderBy('disbursmentDate', 'desc')->first();

            //error if not found
            if ($latestPayPeriod === null || !isset($latestPayPeriod['disbursmentDate'])) {
                return "no dates found";
            }

            // Assuming $latestPayPeriod->disbursmentDate is in "Y-m-d" format
            $disbursmentDate = Carbon::parse($latestPayPeriod->disbursmentDate);

            // Extract the month
            $latestMonth = $disbursmentDate->month;
            $latestYear = $disbursmentDate->year;

            // Get the current date
            $currentDate = Carbon::now();
            $currentMonth = $currentDate->month;
            $currentYear = $currentDate->year;



            // Compare the months and years 
            if ($latestMonth !== $currentMonth || $latestYear !== $currentYear) {
                return response()->json(['error' => 'The latest pay period (' . $disbursmentDate->format('Y-m-d') . ') is not for the current month (' . $currentDate->format('Y-m-d') . '): ' . 'Please set the disbursement date',], 404);
            }

            // If comparison is successful, proceed to fetch the ID
            $latestPayPeriodId = $latestPayPeriod->payPeriodID;
            if ($latestPayPeriodId === null || !isset($latestPayPeriod['disbursmentDate'])) {
                return "no id for the date found";
            }

            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'employeeID' => 'required|string',
                'totalEarnings' => 'required|numeric',
                'totalDeductions' => 'required|numeric',
                'netpay' => 'required|numeric',
            ]);


            // Check if validation fails
            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return response()->json(['payroll validation errors' => $errors], 400);
            }
            // Get the validated data
            $validatedData = $validator->validated();
            // Add additional data
            $additionalData = [
                'payPeriodID' => $latestPayPeriodId,
            ];

            // Combine validated data with additional data
            $dataToUpdate = array_merge($validatedData, $additionalData);

            $payroll->update($dataToUpdate);
            return response()->json(['message' => 'payroll data updated successfully', 'inserted_records' => $payroll], 200);
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
        //do things manually
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
