<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referral; // ✅ Import Referral Model
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        // 1. Capture Referral Code from URL (e.g., ?ref=REF123)
        if ($request->has('ref')) {
            session(['referral_code' => $request->query('ref')]);
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Create the User (Generate their own ref code immediately)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referral_code' => 'REF' . strtoupper(Str::random(6)), // ✅ Auto-generate code
        ]);

        // 3. Process Referral Logic
        $referrerCode = session('referral_code');

        if ($referrerCode) {
            $referrer = User::where('referral_code', $referrerCode)->first();

            // Check if referrer exists and prevent self-referral
            if ($referrer && $referrer->id !== $user->id) {
                Referral::create([
                    'user_id'      => $referrer->id, // The person who invited
                    'referred_id'  => $user->id,     // The new user (Important for tracking funding)
                    'friend_name'  => $user->name,
                    'status'       => 'pending',     // Waiting for wallet funding
                    'bonus_amount' => 200            // Set reward amount
                ]);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}