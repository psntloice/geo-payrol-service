<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;
use Exception;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {

        // return Employee::all();

        try {

            $baseUrl = env('EMPLOYEE_SERVICE_BASE_URL');
            $baseUrlString = (string) $baseUrl;

            $tokn = JWTAuth::getToken();

            if (!$tokn) {
                return response()->json(['error' => 'Token not provided'], 400);
            }

            $tokenString = (string) $tokn;
            // $employeeResponse = Http::withHeaders([
            //     'Authorization' => $token,
            // ])->get('https://geo-employee-service.vercel.app/api/employees/');

            // Make a request to another endpoint with the token in the Authorization header
            $client = new Client();
            // $response = $client->request('GET', 'https://geo-employee-service.vercel.app/api/employees/', [
                Log::info('Request URL: ' . $baseUrlString);
            $response = $client->request('GET',  $baseUrlString . '/employees/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tokenString,
                'Accept' => 'application/json',
                ],
            ]);
            $responseBody = $response->getBody()->getContents();
            Log::info('External service response: ' . $responseBody);

            $statusCode = $response->getStatusCode();


            // return Earning::all();
            // $employeeResponse = Http::get(env('EMPLOYEE_SERVICE_BASE_URL') . '/employees/' );
            if ($statusCode !== 200) {
                Log::error('Failed to fetch employee data from external service: ' . $statusCode);
                return response()->json(['error' => 'Failed to fetch employee data'], 404);
            } else {

                // Process the response as needed
                $employeeResponse = json_decode($response->getBody(), true);

                return response()->json($employeeResponse);
            }
        } catch (Exception $e) {
            Log::error('JWT Token Invalid', ['exception' => $e->getMessage()]);
        }
    }


 
    // public function show(Employee $employee)
    public function show(Request $request, $employeeId)

    {
        try {
            $baseUrl = env('EMPLOYEE_SERVICE_BASE_URL');
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 400);
            }

            $tokenString = (string) $token;

            // Make request to fetch a specific employee
            $client = new Client();
            $response = $client->request('GET', $baseUrl . '/employees/' . $employeeId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tokenString,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                Log::error('Failed to fetch employee data from external service: ' . $statusCode);
                return response()->json(['error' => 'Failed to fetch employee data'], 404);
            }

            // Process the response as needed
            $employee = json_decode($response->getBody(), true);

            return response()->json($employee);
        } catch (\Exception $e) {
            Log::error('Error fetching specific employee', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

   
}
