<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\WalletService;
use App\Models\WalletAudit;
use App\Models\DataPlan; // ✅ Import the Model

class DataController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    /**
     * Show Buy Data page.
     */
    public function index()
    {
        // 1. ✅ Fetch ONLY Active Plans from Database
        // We pass the raw collection to the view; JS handles the filtering
        $plans = DataPlan::where('is_active', true)->get();
        
        $user = Auth::user();

        // 2. Fetch Recent Beneficiaries (UX Feature)
        $wallet = $user->wallet;
        $recents = collect();

        if ($wallet) {
            $recents = $wallet->audits()
                ->where('description', 'LIKE', '%Data%') // Generic match
                ->latest()
                ->take(15)
                ->get()
                ->map(function ($tx) {
                    $raw  = $tx->meta ?? [];
                    $meta = is_string($raw) ? json_decode($raw, true) : (array) $raw;

                    return [
                        'name'    => $meta['phone'] ?? 'Unknown',
                        'phone'   => $meta['phone'] ?? null,
                        'network' => $meta['network'] ?? 'MTN',
                    ];
                })
                ->filter(fn ($item) => ! empty($item['phone']))
                ->unique('phone')
                ->take(5)
                ->values();
        }

        // Pass 'plans' (database) and 'recents' to the view
        return view('vtu.data', compact('plans', 'recents'));
    }

    /**
     * Handle data purchase.
     */
    public function purchase(Request $request)
    {
        // 1. Validate Input
        $data = $request->validate([
            'network'         => ['required', 'string'],
            'phone'           => ['required', 'regex:/^(0\d{10}|234\d{10}|\+234\d{10})$/'],
            'plan_id'         => ['required', 'exists:data_plans,id'], // ✅ Must exist in DB
            'transaction_pin' => ['required', 'digits:4'],
            // 'amount'       => REMOVED! Never trust frontend amount.
        ]);

        $user = Auth::user();

        // 2. 🔒 Transaction PIN Check
        if (! $user->transaction_pin) {
            return redirect()
                ->route('profile.edit')
                ->with('status', 'Please set your Transaction PIN first.');
        }

        if (! Hash::check($data['transaction_pin'], $user->transaction_pin)) {
            return back()->with('error', '❌ Incorrect Transaction PIN.')->withInput();
        }

        // 3. ✅ Fetch Secure Price & Details from DB
        $plan = DataPlan::findOrFail($data['plan_id']);
        
        // Safety Check: Ensure the plan matches the selected network
        if (strtoupper($plan->network) !== strtoupper($data['network'])) {
             return back()->with('error', "Plan mismatch. Please re-select network.")->withInput();
        }

        $amount = (float) $plan->price; // SECURE PRICE
        $reference = 'DATA-' . uniqid(); // Or use Idempotency key if you have it implemented on frontend

        // 4. 🔁 Idempotency (Optional check if you send key)
        if ($request->has('idempotency_key')) {
             $reference = 'DATA-' . $request->input('idempotency_key');
             if ($existing = WalletAudit::where('reference', $reference)->first()) {
                return redirect()->route('transactions.show', $existing)
                    ->with('status', 'Transaction already processed.');
            }
        }

        // 5. Check Balance
        if ($user->wallet->current_balance < $amount) {
            return back()->with('error', 'Insufficient wallet balance.')->withInput();
        }

        try {
            // 6. Debit Wallet & Audit
            $audit = $this->walletService->debit(
                $user,
                $amount,
                'data',
                "Data: {$plan->network} {$plan->name} - {$data['phone']}",
                [
                    'network'      => $plan->network,
                    'phone'        => $data['phone'],
                    'plan_name'    => $plan->name,
                    'api_plan_id'  => $plan->plan_id, // The ID for the API
                    'db_plan_id'   => $plan->id,
                ],
                $reference
            );

            // 7. TODO: Call Real VTU API Here
            // $apiResponse = App\Services\VtuApi::buyData($data['phone'], $plan->network, $plan->plan_id);
            $vtuSucceeded = true; // Simulated Success

            if (! $vtuSucceeded) {
                $this->walletService->refund($audit, 'vtu_failed');
                throw new \RuntimeException('Network provider failed. Refunded.');
            }

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('transactions.show', $audit)
            ->with('success', "✅ {$plan->network} {$plan->name} sent to {$data['phone']} successfully!")
            ->with('autoShare', true);
    }
}