<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataPlan;
use App\Models\AirtimeControl; // ✅ Import the Airtime Model

class PricingController extends Controller
{
    /**
     * Display all pricing controls (Data & Airtime)
     */
    public function index()
    {
        // 1. Fetch Data Plans grouped by Network
        $plans = DataPlan::orderBy('network')
            ->orderBy('price')
            ->get()
            ->groupBy('network');
        
        // 2. ✅ Fetch Airtime Controls
        $airtime = AirtimeControl::all();

        return view('admin.pricing.index', compact('plans', 'airtime'));
    }

    /**
     * Update a single Data Plan's price
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'price'  => 'required|numeric|min:0',
            'status' => 'boolean'
        ]);

        $plan = DataPlan::findOrFail($id);
        
        $plan->update([
            'price'     => $request->price,
            'is_active' => $request->has('status')
        ]);

        return back()->with('success', "Price for {$plan->network} {$plan->name} updated to ₦{$request->price}");
    }

    /**
     * ✅ Update Airtime Discount Percentage
     */
    public function updateAirtime(Request $request, $id)
    {
        $request->validate([
            'discount' => 'required|numeric|min:0|max:100', // Ensure 0-100%
            'status'   => 'boolean'
        ]);

        $control = AirtimeControl::findOrFail($id);
        
        $control->update([
            'discount_percent' => $request->discount,
            'is_active'        => $request->has('status')
        ]);

        return back()->with('success', "{$control->network} Airtime discount updated to {$request->discount}%");
    }
}