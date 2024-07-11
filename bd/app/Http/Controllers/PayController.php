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
use App\Models\PayPeriod;
use Carbon\Carbon;


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

            //call the employees 

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

            //check advance for specific month








            $advancesData = [];
            // Loop through each employee in the results array
            foreach ($advanceJsonData['results'] as $employeeAdvance) {
                // Extract employee id and salary
                $advanceData = [
                    'employee_id' => $employeeAdvance['employee']['id'],
                    'salary' => $employeeAdvance['employee']['salary'],
                ];
                // Add data to the array
                $advancesData[] = $advanceData;
            }


            //do the maths         

            //initialize the variables
            $payPeriodID = $latestPayPeriodId;
            $netPayData = [];

            //for each employee in the employee array calculate netpay
            foreach ($employeesData as $employee) {
                //take employee id and their salary and place advance value as 0
                $employeeId = $employee['employee_id'];
                $salary = $employee['salary'];
                $advance = 0; // Default advance

                // Find advance for the employee
                foreach ($advancesData as $advanceData) {
                    //if advance exists place it as the value
                    if ($advanceData['employee_id'] === $employeeId) {
                        $advance += $advanceData['salary'];
                    }
                }
                // Calculate net pay
                $netPay = $salary - ($advance ?: 0);

                // Add net pay data to the array
                $netPayData[] = [
                    'payPeriodID' => $payPeriodID,
                    'employeeID' => $employeeId,
                    'totalEarnings' => floatval($employee['salary']),
                    'totalDeductions' => $advance,
                    'netpay' => $netPay,
                ];
            }
// return $netPayData;
            // return [
            //     'salary data' => $employeesData,
            //     'advance data' => $advancesData,
            //     'netpay' => $netPayData,
            // ];
            //post the data

            // Validate incoming request
            // Define the validation rules
            $rules = [
                '*.payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
                 '*.employeeID' => 'required|string',
                '*.totalEarnings' => 'required|numeric',
                '*.totalDeductions' => 'required|numeric',
                '*.netpay' => 'required|numeric',
            ];
            $validator = Validator::make($netPayData, $rules);
            // return $validator;
            // Check if validation fails
            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return response()->json(['payroll validation errors' => $errors], 400);
            } else {
                $insertedRecords = [];

                foreach ($netPayData as $data) {
                    $insertedRecord = Payroll::create([
                        'payPeriodID' => $data['payPeriodID'],
                        'employeeID' => $data['employeeID'],
                        'totalEarnings' => $data['totalEarnings'],
                        'totalDeductions' => $data['totalDeductions'],
                        'netpay' => $data['netpay'],
                    ]);
            
                    // Optionally, you can add the inserted record to the response array
                    $insertedRecords[] = $insertedRecord;
                }
                // return "did it";
                return response()->json(['message' => 'Data inserted successfully', 'inserted_records' => $insertedRecords], 200);            }
        } catch (\Exception $e) {
            Log::error('Exception caught', ['payroll creation exception' => $e]);
            return response()->json(['error' => 'An unexpected error occurred when creating a payroll'], 500);
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
 

//you can aggregate advances
// $totalAdvances = [];
// foreach ($advancesData as $advance) {
//     $employeeId = $advance['employee_id'];
//     if (!isset($totalAdvances[$employeeId])) {
//         $totalAdvances[$employeeId] = 0;
//     }
//     $totalAdvances[$employeeId] += $advance['amount'];
// }
