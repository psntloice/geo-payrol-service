<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DeductionController extends Controller
{
    public function index()
    {
        return Deduction::all();
    }

    public function store(Request $request)
    {

        try {
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'payPeriodID' => 'required|string|exists:pay_periods,payPeriodID',
                'employeeID' => 'required|string',
                'deductionType' => 'required|string',
                'amount' => 'required|numeric',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            } else {
                return Deduction::create($validator->validated());
            }
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function show($id)
    {
        $deduction = Deduction::where('deductionID', $id)->first();
        if (!$deduction) {
            return response()->json(['message' => 'not found'], 404);
        }
        return $deduction;
    }

    public function update(Request $request, $id)
    {

        try {
            $deduction = Deduction::where('deductionID', $id)->first();
            if (!$deduction) {
                return response()->json(['message' => 'not found'], 404);
            }
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'payPeriodID' => 'required|string|exists:pay_periods,payPeriodID',
                'employeeID' => 'required|string',
                'deductionType' => 'required|string',
                'amount' => 'required|numeric',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            } else {
                $deduction->update($validator->validated());

                return $deduction;
            }
        } catch (ValidationException $e) {
            // Log validation errors
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        }
    }

    public function destroy($id)
    {
        $deduction = Deduction::where('deductionID', $id)->first();
        if (!$deduction) {
            return response()->json(['message' => 'not found'], 404);
        }
        $deduction->delete();

        return response()->json(['message' => 'deleted successfully']);;
    }
}
