<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VTUController extends Controller
{
    /**
     * Display the VTU main dashboard (Airtime, Data, Cable, Electricity).
     */
    public function index()
    {
        // If you want to show wallet balance later:
        // $balance = Auth::user()->wallet->balance ?? 0;
        // return view('vtu.dashboard', compact('balance'));

        return view('vtu.dashboard');
    }
    
}
