<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    public function index()
    {
        return Deduction::all();
    }

    public function store(Request $request)
    {
       
        $validated = $request->validate([
            'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
            'employeeID' => 'required|integer',
            'deductionType' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        return Deduction::create($validated);
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
            


        $deduction = Deduction::where('deductionID', $id)->first();
        if (!$deduction) {
            return response()->json(['message' => 'not found'], 404);
        }
        $validated = $request->validate([
            'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
            'employeeID' => 'required|integer',
            'deductionType' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        $deduction->update($validated);

        return $deduction;
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
