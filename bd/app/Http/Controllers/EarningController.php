<?php

namespace App\Http\Controllers;

use App\Models\Earning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class EarningController extends Controller
{
    public function index()
    {       
        return Earning::all();  

    }

    public function store(Request $request)
    {
        try {
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
                'employeeID' => 'required|string',
                'earningType' => 'required|string',
                'amount' => 'required|numeric',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            } else {
               return Earning::create($validator->validated());

            }
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
       
    
    }

    public function show($id)
    {
        $earning = Earning::where('earningID', $id)->first();
        if (!$earning) {
            return response()->json(['message' => 'not found'], 404);
        }
        return $earning;
    }

    public function update(Request $request, $id)
    {
        
        try {
            $earning = Earning::where('earningID', $id)->first();
        if (!$earning) {
            return response()->json(['message' => 'not found'], 404);
        }
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
                'employeeID' => 'required|string',
                'earningType' => 'required|string',
                'amount' => 'required|numeric',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            } else {
                $earning->update($validator->validated());

        return $earning;

            }
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy($id)
    {
        $earning = Earning::where('earningID', $id)->first();
        if (!$earning) {
            return response()->json(['message' => 'not found'], 404);
        }
        $earning->delete();

        return response()->json(['message' => 'deleted successfully']);;
    }
}
