<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

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

                return "you won";
                
            }
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }
    public function me(Request $request)
    {
        return "beautiful";
    }
}
