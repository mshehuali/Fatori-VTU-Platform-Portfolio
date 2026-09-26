<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PinController extends Controller
{
    /**
     * Update the user's transaction PIN.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Validation
        $request->validate([
            'current_password' => ['required', 'current_password'], // Security check
            'pin'              => ['required', 'digits:4', 'confirmed'],
        ]);

        // Save PIN (Hashed for security)
        $user->transaction_pin = Hash::make($request->pin);
        $user->save();

        return back()->with('status', 'pin-updated');
    }
}