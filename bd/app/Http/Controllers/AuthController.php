<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Exception;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'payload' => 'required',
                'type' => 'required',
                'message' => 'required',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            } else {
                $validatedData = $validator->validated();


                $mainobject = $request->input('payload');
                $type = $request->input('type');
                $message = $request->input('message');


                $employeeEmail = $mainobject['employee']['email'];
                $ownersemail = $request->get('email');
                $role = $request->get('role');
                try {
                    // Your code here
                } catch (Exception $e) {
                    Log::error('Error in AuthController@index: ' . $e->getMessage());
                    return response()->json(['error' => 'Internal Server Error'], 500);
                }

                return "you won";
                
            }
        } catch (Exception $e) {
            // Log validation errors
            // Log::error('Validation Error', ['errors' => $e->errors()]);
            // return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
            Log::error('Error in AuthController@index: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    public function me(Request $request)
    {
        dd("me");
        return "beautiful";
    }
    public function memo()
    {
        dd("memo");
        return "beautiful people";
    }
}
