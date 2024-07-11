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
use DateTime;


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
                    'email' => $employee['email'],

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
                $advanceData = [
                    'employee_id' => $employeeAdvance['employee']['id'],
                    'salary' => $employeeAdvance['employee']['salary'],
                    'approvalState' => $employeeAdvance['is_approved'],
                    'advanceDate' => $employeeAdvance['date'],
                ];
                $advancesData[] = $advanceData;
            }

            //do the maths         

            //initialize the variables
            $payPeriodID = $latestPayPeriodId;
            $netPayData = [];
            $notificationPayData = [];
            //for each employee in the employee array calculate netpay
            foreach ($employeesData as $employee) {
                //take employee id and their salary and place advance value as 0
                $employeeId = $employee['employee_id'];
                $employeeEmail = $employee['email'];
                $salary = $employee['salary'];
                $advance = 0; // Default advance

                // Find advance for the employee
                foreach ($advancesData as $advanceData) {
                    $advanceDate = new DateTime($employeeAdvance['date']);
                    $advanceYear = $advanceDate->format('Y');
                    $advanceMonth = $advanceDate->format('m');
                    //if advance exists place it as the value
                    if ($advanceData['employee_id'] === $employeeId && $advanceData['approvalState'] && $advanceYear == $currentYear && $advanceMonth == $currentMonth) {
                        $advance += $advanceData['salary'];
                    }
                }
                // Calculate net pay
                $netPay = $salary - ($advance ?: 0);

                // Add net pay data to the array for insertion to payment table
                $netPayData[] = [
                    'payPeriodID' => $payPeriodID,
                    'employeeID' => $employeeId,
                    'totalEarnings' => floatval($employee['salary']),
                    'totalDeductions' => $advance,
                    'netpay' => $netPay,
                ];
                // Add net pay data to the array for insertion to notification table
                $notificationPayData[] = [
                    'payPeriodID' => $payPeriodID,
                    'employee' => [
                        'id' => $employeeId,
                        'email' => $employeeEmail,
                    ],
                    'totalEarnings' => floatval($employee['salary']),
                    'totalDeductions' => $advance,
                    'netpay' => $netPay,
                ];
            }

            //post the data

            // Validate incoming request
            $rules = [
                '*.payPeriodID' => 'required|string|exists:pay_periods,payPeriodID',
                '*.employeeID' => 'required|string',
                '*.totalEarnings' => 'required|numeric',
                '*.totalDeductions' => 'required|numeric',
                '*.netpay' => 'required|numeric',
            ];
            $validator = Validator::make($netPayData, $rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return response()->json(['payroll validation errors' => $errors], 400);
            }

            //create payment
            $insertedRecords = [];
            foreach ($netPayData as $data) {
                $insertedRecord = Payroll::create([
                    'payPeriodID' => $data['payPeriodID'],
                    'employeeID' => $data['employeeID'],
                    'totalEarnings' => $data['totalEarnings'],
                    'totalDeductions' => $data['totalDeductions'],
                    'netpay' => $data['netpay'],
                ]);
                $insertedRecords[] = $insertedRecord;
            }
            if (!$insertedRecords) {
                return response()->json(['error' => 'payment data has not been inserted'], 400);
            }
            if (!$notificationPayData) {
                return response()->json(['error' => 'notifications for payment data has not been inserted'], 400);
            }


            //make notifications for payment

            //takes care of url
            $baseUrl = env('NOTIFICATION_SERVICE_BASE_URL');
            $baseUrlString = (string) $baseUrl;

            //takes care of the token *********************************should have try and catch
            $tokn = JWTAuth::getToken();
            if (!$tokn) {
                return response()->json(['error' => 'Token not provided'], 400);
            }
            $tokenString = (string) $tokn;

            //connects to the external service
            $client = new Client();
            Log::info('Request URL: ' . $baseUrlString);

            //set data to be sent to  external service
            $monthName = $disbursmentDate->monthName;
            $paymentMessage = "Payment for $monthName $latestYear Disbursed";
            $type = 'payment';

            //insert that data to the notifications table each by each and store all the responses
            $notificationResponses = [];
            foreach ($notificationPayData as $notifcreate) {
                Log::info($notifcreate);
                // Prepare data to send              
                $objectBody = $notifcreate;

                // Validate data
                if (!isset($paymentMessage) || !isset($objectBody) || !isset($type)) {
                    return response()->json(['error' => 'No data for notification provided'], 400);
                }

                // Send notification
                $response = $client->request('POST', $baseUrlString . '/notifications/', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $tokenString,
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'message' => $paymentMessage,
                        'type' => $type,
                        'payload' => $objectBody,
                    ],
                ]);

                // Handle response as needed
                $statusCode = $response->getStatusCode();
                $responseData = json_decode($response->getBody(), true); // Assuming the response is JSON

                if (!$responseData) {
                    $notificationResponses[] = ['error' => 'Failed to send notification', 'statusCode' => $statusCode];
                }
                $notificationResponses[] = $responseData;
            }
            return response()->json(['message' => 'Data inserted successfully', 'inserted_records' => $insertedRecords, 'notification_records' => $notificationResponses], 200);
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
                'payPeriodID' => 'required|string|exists:pay_periods,payPeriodID',
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
