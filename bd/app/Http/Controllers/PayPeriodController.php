<?php

namespace App\Http\Controllers;

use App\Models\PayPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayPeriodController extends Controller
{
    public function index()
    {
        return PayPeriod::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payPeriodStart' => 'required|date',
            'payPeriodEnd' => 'required|date',
            'paydate' => 'required|date',

        ]);

        return PayPeriod::create($validated);
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
        $payPeriod = PayPeriod::where('payPeriodID', $id)->first();
        if (!$payPeriod) {
            return response()->json(['message' => 'not found'], 404);
        }
        else{
        $validated = $request->validate([
            'payPeriodStart' => 'required|date',
            'payPeriodEnd' => 'required|date',
            'paydate' => 'required|date',
        ]);

        $payPeriod->update($validated);

        return $payPeriod;
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