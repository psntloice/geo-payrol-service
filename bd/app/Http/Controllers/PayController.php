<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class PayController extends Controller
{
    public function index()
    {
        // return Payroll::with('employee')->get();
        return Payroll::all();
    }

    public function store(Request $request)
    {
        try {
            //call the employees first

            //check url
            $baseUrl = env('EMPLOYEE_SERVICE_BASE_URL');
            $baseUrlString = (string) $baseUrl;

            //check auth
            $tokn = JWTAuth::getToken();
            if (!$tokn) {
                return response()->json(['error' => 'Token not provided'], 400);
            }
            $tokenString = (string) $tokn;

            //connect with external employee check for salary 
            $salaryresponse =  Http::accept('application/json')->withHeaders([
                'Authorization' => 'Bearer ' . $tokenString,
                'Accept' => 'application/json',
            ])->get($baseUrlString . '/employees/');
            $jsonData = $salaryresponse->json();

            //if connection not made
            if (!$jsonData) {
                Log::error('Failed to fetch employee salary data from external service ');
                return response()->json(['error' => 'Failed to fetch employee salary data'], 404);
            }



            //pick their earnings 
            $employeesData = [];
            // Loop through each employee in the results array
            foreach ($jsonData['results'] as $employee) {
                // Extract employee id and salary
                $employeeData = [
                    'employee_id' => $employee['id'],
                    'salary' => $employee['salary'],
                ];

                // Add employee data to the array
                $employeesData[] = $employeeData;
            }


            //connect with external employee check for advance 
            $advanceResponse =  Http::accept('application/json')->withHeaders([
                'Authorization' => 'Bearer ' . $tokenString,
                'Accept' => 'application/json',
            ])->get($baseUrlString . '/advances/');
            $advanceJsonData = $advanceResponse->json();

            //if connection not made
            if (!$advanceJsonData) {
                Log::error('Failed to fetch employee advance data from external service ');
                return response()->json(['error' => 'Failed to fetch employee advance data'], 404);
            }



            //pick their deductions

            $advancesData = [];
            // Loop through each employee in the results array
            foreach ($advanceJsonData['results'] as $employeeAdvance) {
                // Extract employee id and salary
                $advanceData = [
                    'employee_id' => $employeeAdvance['employee']['id'],
                    'salary' => $employeeAdvance['employee']['salary'],
                ];

                // Add employee data to the array
                $advancesData[] = $advanceData;
            }
            return [
                'salary' => $employeesData,
                'advance' => $advancesData,
            ];

            //do the maths

            //post the data

            // Validate incoming request
            // $validator = Validator::make($request->all(), [
            //    'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
            //     'employeeID' => 'required|string',
            //          'amount' => 'required|numeric',
            //     'totalEarnings' => 'required|numeric',
            //     'totalDeductions' => 'required|numeric',
            //     'netpay' => 'required|numeric',
            // ]);

            // // Check if validation fails
            // if ($validator->fails()) {
            //     return response()->json(['error' => $validator->errors()], 400);
            // } else {
            //    return Payroll::create($validator->validated());

            // }
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

    public function update(Request $request, $id)
    {
        try {
            $payroll = Payroll::where('id', $id)->first();
            if (!$payroll) {
                return response()->json(['message' => 'not found'], 404);
            }
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
                'employeeID' => 'required|string',
                'amount' => 'required|numeric',
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
