<?php

namespace App\Http\Controllers;

use App\Models\Earning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EarningController extends Controller
{
    public function index()
    {       
        return Earning::all();  

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
            'employeeID' => 'required|integer',
            'earningType' => 'required|string',
            'amount' => 'required|numeric',
        ]);
        Log::info('Validated data:', $validated);
        return Earning::create($validated);
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
        $earning = Earning::where('earningID', $id)->first();
        if (!$earning) {
            return response()->json(['message' => 'not found'], 404);
        }
        $validated = $request->validate([
            'payPeriodID' => 'required|integer|exists:pay_periods,payPeriodID',
            'employeeID' => 'required|integer',
            'earningType' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        $earning->update($validated);

        return $earning;
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
