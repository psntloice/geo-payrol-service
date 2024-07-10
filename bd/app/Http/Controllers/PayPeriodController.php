<?php

namespace App\Http\Controllers;

use App\Models\PayPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

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
                'disbursmentDate' => 'required|date',

            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            } else {
               return PayPeriod::create($validator->validated());

            }
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
            $payPeriod = PayPeriod::where('payPeriodID', $id)->first();
            if (!$payPeriod) {
                return response()->json(['message' => 'not found'], 404);
            }
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'disbursmentDate' => 'required|date',

             ]);
 

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            } else {
                $payPeriod->update($validator->validated());

        return $payPeriod;

            }
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