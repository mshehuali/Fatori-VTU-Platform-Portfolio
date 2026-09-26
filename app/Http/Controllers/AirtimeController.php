<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\WalletService;
use App\Models\Wallet;
use App\Models\WalletAudit;
use App\Models\AirtimeControl; // ✅ Import Airtime Model
use App\Mail\TransactionReceiptMail;

class AirtimeController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function index()
    {
        $user = Auth::user();

        // 1. ✅ FETCH ACTIVE DISCOUNTS
        // Returns ['MTN' => 2.00, 'AIRTEL' => 1.50, ...]
        $discounts = AirtimeControl::where('is_active', true)->pluck('discount_percent', 'network');

        // 2. FETCH RECENT TRANSACTIONS
        $wallet = Wallet::where('user_id', $user->id)->first();
        $recents = collect();

        if ($wallet) {
            $recents = $wallet->audits()
                ->where('description', 'LIKE', '%Airtime%')
                ->latest()
                ->take(15)
                ->get()
                ->map(function ($tx) {
                    $raw = $tx->meta ?? [];
                    $meta = is_string($raw) ? json_decode($raw, true) : (array) $raw;

                    return [
                        'name'    => $meta['phone'] ?? 'Unknown',
                        'phone'   => $meta['phone'] ?? null,
                        'network' => $meta['network'] ?? 'MTN',
                    ];
                })
                ->filter(fn($item) => !empty($item['phone']))
                ->unique('phone')
                ->take(5)
                ->values();
        }

        return view('vtu.airtime', compact('recents', 'discounts'));
    }

    public function purchase(Request $request)
    {
        // 1. Validation
        $data = $request->validate([
            'network'         => ['required', 'string'],
            'phone'           => ['required', 'regex:/^(0\d{10}|234\d{10}|\+234\d{10})$/'],
            'amount'          => ['required', 'numeric', 'min:50'],
            'transaction_pin' => ['required', 'digits:4'],
            'idempotency_key' => ['required', 'string'],
        ]);

        $user = Auth::user();

        // 2. 🔒 SECURITY CHECK: PIN
        if (!$user->transaction_pin) {
            return back()->with('error', 'Please set your Transaction PIN in profile settings first.')->withInput();
        }

        if (!Hash::check($data['transaction_pin'], $user->transaction_pin)) {
            return back()->with('error', 'Incorrect Transaction PIN.')->withInput();
        }

        $inputAmount = round((float)$data['amount'], 2);
        $reference = 'AIRTIME-' . $data['idempotency_key'];

        try {
            // 3. ✅ CALCULATE DISCOUNTED PRICE
            $control = AirtimeControl::where('network', $data['network'])->first();

            if (!$control || !$control->is_active) {
                return back()->with('error', "Airtime for {$data['network']} is currently unavailable.")->withInput();
            }

            $discountPercent = $control->discount_percent;
            $discountAmount  = round($inputAmount * ($discountPercent / 100), 2);
            $payableAmount   = $inputAmount - $discountAmount; // User pays this

            // 4. Check Balance (Against Payable Amount)
            if ($user->wallet->current_balance < $payableAmount) {
                return back()->with('error', "Insufficient balance. You need ₦" . number_format($payableAmount, 2))->withInput();
            }

            // 5. Idempotency Check
            if ($existing = WalletAudit::where('reference', $reference)->first()) {
                return redirect()->route('transactions.show', $existing)
                    ->with('status', 'Transaction already processed.');
            }

            // 6. Execute Debit
            $audit = $this->walletService->debit(
                $user,
                $payableAmount, // Debit the discounted amount
                'airtime',
                "Airtime: {$data['network']} ₦{$inputAmount} to {$data['phone']}",
                [
                    'network'          => $data['network'],
                    'phone'            => $data['phone'],
                    'original_amount'  => $inputAmount,
                    'discount_percent' => $discountPercent . '%',
                    'discount_value'   => $discountAmount,
                ],
                $reference
            );

            // 7. Simulated VTU Success (Integration point)
            $vtuSucceeded = true; 
            // $vtuSucceeded = App\Services\VtuApi::purchaseAirtime(...);

            if (!$vtuSucceeded) {
                $this->walletService->refund($audit, 'vtu_failed');
                throw new \RuntimeException('Network provider failed. Refunded.');
            }

            session()->forget('airtime_form_key');

            // 8. Send Receipt
            try {
                Mail::to($user->email)->send(new TransactionReceiptMail($audit));
            } catch (\Throwable $e) {
                // Log email failure
            }

        } catch (\Throwable $e) {
            if (str_contains(strtolower($e->getMessage()), 'frozen')) {
                return back()->with('error', 'Wallet is temporarily restricted.')->withInput();
            }
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('transactions.show', $audit)
            ->with('success', "Airtime sent! You saved ₦" . number_format($discountAmount, 2))
            ->with('autoShare', true);
    }
}