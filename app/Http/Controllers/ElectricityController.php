<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Services\WalletService;
use App\Mail\TransactionReceiptMail;
use App\Models\WalletAudit;

class ElectricityController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function index()
    {
        $user   = Auth::user();
        $wallet = $user->wallet; // guaranteed by middleware
        $recents = collect();

        if ($wallet) {
            $recents = $wallet->audits()
                ->where('description', 'LIKE', '%Electricity%')
                ->latest()
                ->take(15)
                ->get()
                ->map(function ($tx) {
                    $raw  = $tx->meta ?? [];
                    $meta = is_string($raw) ? json_decode($raw, true) : (array) $raw;

                    return [
                        'name'     => $meta['meter_no'] ?? 'Unknown',
                        'meter_no' => $meta['meter_no'] ?? null,
                        'disco'    => $meta['disco'] ?? 'IKEDC',
                    ];
                })
                ->filter(fn ($item) => ! empty($item['meter_no']))
                ->unique('meter_no')
                ->take(5)
                ->values();
        }

        return view('vtu.electricity', compact('recents'));
    }

    public function purchase(Request $request)
    {
        $data = $request->validate([
            'disco'            => ['required','string'],
            'meter_type'       => ['required','string'],
            'meter_no'         => ['required','string'],
            'phone'            => ['required','regex:/^(0\d{10}|234\d{10}|\+234\d{10})$/'],
            'amount'           => ['required','numeric','min:500'],
            'transaction_pin'  => ['required','digits:4'],
            'idempotency_key'  => ['required','string'],
        ]);

        $user = Auth::user();

        // 🔒 Transaction PIN check
        if (! $user->transaction_pin) {
            return back()->with('error', 'Please set your Transaction PIN first.')->withInput();
        }

        if (! Hash::check($data['transaction_pin'], $user->transaction_pin)) {
            return back()->with('error', '❌ Incorrect Transaction PIN.')->withInput();
        }

        $amount    = round((float) $data['amount'], 2);
        $reference = 'ELEC-' . $data['idempotency_key'];

        // 🔁 Idempotency
        if ($existing = WalletAudit::where('reference', $reference)->first()) {
            return redirect()
                ->route('transactions.show', $existing)
                ->with('status', 'Transaction already processed.');
        }

        try {
            $audit = $this->walletService->debit(
                $user,
                $amount,
                'electricity',
                "Electricity token for meter {$data['meter_no']}",
                [
                    'disco'      => $data['disco'],
                    'meter_type' => $data['meter_type'],
                    'meter_no'   => $data['meter_no'],
                    'phone'      => $data['phone'],
                ],
                $reference
            );

            // TODO: Replace with real electricity API
            $token = 'DEMO-TOKEN-' . strtoupper(Str::random(8));
            $vtuSucceeded = true;

            if (! $vtuSucceeded) {
                $this->walletService->refund($audit, 'vtu_failed');
                throw new \RuntimeException('Electricity purchase failed — refunded.');
            }

            // Attach token to audit metadata
            $meta = is_array($audit->meta) ? $audit->meta : [];
            $meta['token'] = $token;
            $audit->meta = $meta;
            $audit->save();

            session()->forget('electricity_form_key');

            try {
                Mail::to($user->email)->send(new TransactionReceiptMail($audit));
            } catch (\Throwable $e) {
                // Email failure must not affect transaction
            }

        } catch (\Throwable $e) {
            if ($existing = WalletAudit::where('reference', $reference)->first()) {
                return redirect()->route('transactions.show', $existing);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('transactions.show', $audit)
            ->with('success', '✅ Electricity token purchased successfully.')
            ->with('autoShare', true);
    }
}
