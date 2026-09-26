<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;  // ✅ ADDED
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\TransactionReceiptMail;
use App\Services\WalletService;
use App\Models\Wallet;
use App\Models\WalletAudit;

class CableController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function index()
    {
        // 1. FETCH RECENT CABLE TRANSACTIONS
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $recents = collect();

        if ($wallet) {
            $recents = $wallet->audits()
                // Filter where description looks like a Cable transaction
                ->where(function ($query) {
                    $query->where('description', 'LIKE', '%DSTV%')
                          ->orWhere('description', 'LIKE', '%GOtv%')
                          ->orWhere('description', 'LIKE', '%StarTimes%')
                          ->orWhere('description', 'LIKE', '%Showmax%');
                })
                ->latest()
                ->take(20)
                ->get()
                ->map(function ($tx) {
                    // Extract Metadata
                    $raw = $tx->meta ?? $tx->metadata ?? $tx->details ?? [];
                    $meta = is_string($raw) ? json_decode($raw, true) : (array) $raw;

                    return [
                        'name'      => $meta['smartcard'] ?? 'Unknown',
                        'smartcard' => $meta['smartcard'] ?? null,
                        'provider'  => $meta['provider'] ?? 'DSTV',
                    ];
                })
                ->filter(function ($item) {
                    return !empty($item['smartcard']);
                })
                ->unique('smartcard')
                ->take(5)
                ->values();
        }

        return view('vtu.cable', compact('recents'));
    }

    public function purchase(Request $request)
    {
        $data = $request->validate([
            'provider'          => ['required','string'],
            'smartcard'         => ['required','string'],
            'package'           => ['required','string'],
            'amount'            => ['required','numeric','min:500'],
            'transaction_pin'   => ['required', 'digits:4'], // 🔒 PIN REQUIRED
            'idempotency_key'   => ['required','string'],
        ]);

        $user = Auth::user();

        // 🔒 PIN VERIFICATION (NIGERIAN FINTECH STANDARD)
        if (!$user->transaction_pin) {
            return back()
                ->with('error', 'Please set your Transaction PIN in Profile settings first.')
                ->withInput();
        }

        if (!Hash::check($data['transaction_pin'], $user->transaction_pin)) {
            return back()
                ->with('error', '❌ Incorrect Transaction PIN. Try again.')
                ->withInput();
        }

        $amount = round((float)$data['amount'], 2);
        $reference = 'CABLE-'.$data['idempotency_key'];

        // Idempotency check
        if ($existing = WalletAudit::where('reference', $reference)->first()) {
            return redirect()
                ->route('transactions.show', $existing)
                ->with('status', 'Transaction already processed.');
        }

        try {
            $audit = $this->walletService->debit(
                $user,
                $amount,
                'cable',
                "{$data['provider']} {$data['package']} for {$data['smartcard']}",
                [
                    'provider'  => $data['provider'],
                    'smartcard' => $data['smartcard'],
                    'package'   => $data['package'],
                ],
                $reference
            );

            // TODO: Connect Real Cable TV API Here
            $vtuSucceeded = true;

            if (!$vtuSucceeded) {
                $this->walletService->refund($audit, 'vtu_failed');
                throw new \RuntimeException('Cable payment failed — refunded.');
            }

            session()->forget('cable_form_key');

        } catch (\Throwable $e) {
            // Handle race conditions
            if ($existing = WalletAudit::where('reference', $reference)->first()) {
                return redirect()->route('transactions.show', $existing);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('transactions.show', $audit)
            ->with('success', "✅ {$data['provider']} subscription successful.")
            ->with('autoShare', true);
    }
}
