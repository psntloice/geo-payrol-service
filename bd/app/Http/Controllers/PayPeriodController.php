<?php

namespace App\Http\Controllers;

use App\Models\PayPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use DateTime;
use Carbon\Carbon;

class PayPeriodController extends Controller
{
    public function index()
    {
        return PayPeriod::all();
    }

    public function store(Request $request)
    {
        try {
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'disbursmentDate' => 'required|date_format:d/m/Y',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
            $disbursmentDate = Carbon::createFromFormat('d/m/Y', $request->input('disbursmentDate'))->format('Y-m-d H:i:s');
            $paycreate = PayPeriod::create([
                'disbursmentDate' => $disbursmentDate,
            ]);
            if ($paycreate) {

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

                //picks the data to be sent to  external service
                //date
                $respData = json_decode($paycreate, true);
                $disbursmentDate = $respData['disbursmentDate'];
                $dateObj = new DateTime($disbursmentDate);
                $monthName = $dateObj->format('F');
                $message = "Disbursment date for $monthName Open";

                //payload
                $dataToSend = $paycreate->toArray();
                unset($dataToSend['created_at']);
                unset($dataToSend['updated_at']);

                $objectBody = $dataToSend;
                $type = 'disbursement_date';

                //validates if the data is okay 
                if (!isset($message) || !isset($objectBody) || !isset($type)) {
                    return response()->json(['error' => 'No data for notification provided'], 400);
                }

                //connects to the external as it posts the data
                $response = $client->request('POST', $baseUrlString . '/notifications/', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $tokenString,
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'message' => $message,
                        'type' => $type,
                        'payload' => $objectBody,                            // Add more data as needed
                    ],
                ]);
                $responseBody = $response->getBody()->getContents();
                Log::info('External service response: ' . $responseBody);

                Log::info('here');

                //checks if the message was sent successfully
                $statusCode = $response->getStatusCode();

                if (!isset($responseBody)) {
                    Log::error('Failed to post data to external service:' . $statusCode);
                    return response()->json(['error' => 'Failed to post notification data',  'pay period' => $paycreate]);
                }

                //returns payperiod success message 
                return response()->json([
                    'success' => true,
                    'notification' => "notification sent successfully",
                    'pay period' => $paycreate
                ], 200);
            } else {
                return "failed to create disbursment date";
            }


            //    return PayPeriod::create($validator->validated());           
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function show($id)
    {
        $payPeriod =  PayPeriod::where('payPeriodID', $id)->first();
        if (!$payPeriod) {
            return response()->json(['message' => 'not found'], 404);
        }
        return $payPeriod;
    }

    public function update(Request $request, $id)
    {        
        try {
            //check if payperiod is available
            $payPeriod = PayPeriod::where('payPeriodID', $id)->first();
            if (!$payPeriod) {
                return response()->json(['message' => 'not found'], 404);
            }
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'disbursmentDate' => 'required|date_format:d/m/Y',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
            $disbursmentDate = Carbon::createFromFormat('d/m/Y', $request->input('disbursmentDate'))->format('Y-m-d H:i:s');
            // $paycreate = PayPeriod::create([
            //     'disbursmentDate' => $disbursmentDate,
            // ]);
            $payPeriod->update($validator->validated());
            if ($payPeriod) {

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

                //picks the data to be sent to  external service
                //date
                $respData = json_decode($payPeriod, true);
                $disbursmentDate = $respData['disbursmentDate'];
                $dateObj = new DateTime($disbursmentDate);
                $monthName = $dateObj->format('F');
                $message = "Disbursment date for $monthName Open";

                //payload
                $dataToSend = $payPeriod->toArray();
                unset($dataToSend['created_at']);
                unset($dataToSend['updated_at']);

                $objectBody = $dataToSend;
                $type = 'disbursement_date';

                //validates if the data is okay 
                if (!isset($message) || !isset($objectBody) || !isset($type)) {
                    return response()->json(['error' => 'No data for notification provided'], 400);
                }

                //connects to the external as it posts the data
                $response = $client->request('POST', $baseUrlString . '/notifications/', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $tokenString,
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'message' => $message,
                        'type' => $type,
                        'payload' => $objectBody,                            // Add more data as needed
                    ],
                ]);
                $responseBody = $response->getBody()->getContents();
                Log::info('External service response: ' . $responseBody);

                Log::info('here');

                //checks if the message was sent successfully
                $statusCode = $response->getStatusCode();

                if (!isset($responseBody)) {
                    Log::error('Failed to post data to external service:' . $statusCode);
                    return response()->json(['error' => 'Failed to post notification data',  'pay period' => $payPeriod]);
                }

                //returns payperiod success message 
                return response()->json([
                    'success' => true,
                    'notification' => "notification pay period update sent successfully",
                    'pay period' => $payPeriod
                ], 200);
            } else {
                return "failed to create disbursment date";
            }
            return $payPeriod;                        
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }


    public function destroy($id)
    {
        $payPeriod = PayPeriod::where('payPeriodID', $id)->first();
        Log::info($payPeriod);
        if (!$payPeriod) {
            return response()->json(['message' => 'not found'], 404);
        }
        $payPeriod->delete();

        return response()->json(['message' => 'deleted successfully']);;
    }
}
