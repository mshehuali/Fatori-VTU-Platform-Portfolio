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

class EducationController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function index()
    {
        $services = [
            [
                'code' => 'waec_pin',
                'name' => 'WAEC Result Checker',
                'price' => 2500,
                'color' => 'yellow',
                'icon'  => 'W'
            ],
            [
                'code' => 'neco_pin',
                'name' => 'NECO Result Checker',
                'price' => 2200,
                'color' => 'green',
                'icon'  => 'N'
            ],
            [
                'code' => 'jamb_pin',
                'name' => 'JAMB e-PIN',
                'price' => 4500,
                'color' => 'emerald',
                'icon'  => 'J'
            ],
        ];

        return view('vtu.education', compact('services'));
    }

    public function purchase(Request $request)
    {
        $data = $request->validate([
            'exam_type'         => ['required','string'],
            'quantity'          => ['required','integer','min:1'],
            'delivery_email'    => ['nullable','email'],
            'phone'             => ['nullable','regex:/^(0\d{10}|234\d{10}|\+234\d{10})$/'],
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

        // Service Definitions
        $services = [
            ['code'=>'waec_pin','name'=>'WAEC Result Checker','price'=>2500],
            ['code'=>'neco_pin','name'=>'NECO Result Checker','price'=>2200],
            ['code'=>'jamb_pin','name'=>'JAMB e-PIN','price'=>4500],
        ];

        $service = collect($services)->firstWhere('code', $data['exam_type']);

        if (!$service) {
            return back()->withErrors(['exam_type' => 'Invalid exam type.'])->withInput();
        }

        $quantity    = (int)$data['quantity'];
        $unitPrice   = (float)$service['price'];
        $amount      = $unitPrice * $quantity;
        $serviceName = $service['name'];
        $reference   = 'EDU-'.$data['idempotency_key'];

        // 1. Idempotency Check
        if ($existing = WalletAudit::where('reference', $reference)->first()) {
            return redirect()
                ->route('transactions.show', $existing)
                ->with('status', 'Transaction already processed.');
        }

        try {
            // Generate Demo PINs
            $pins = [];
            for ($i = 0; $i < $quantity; $i++) {
                $pins[] = 'PIN-' . strtoupper(Str::random(12));
            }

            // 2. Use WalletService
            $audit = $this->walletService->debit(
                $user,
                $amount,
                'education',
                "{$serviceName} x{$quantity}",
                [
                    'service_code'    => $service['code'],
                    'service_name'    => $serviceName,
                    'quantity'        => $quantity,
                    'unit_price'      => $unitPrice,
                    'delivery_email'  => $data['delivery_email'] ?? $user->email,
                    'phone'           => $data['phone'] ?? null,
                    'pins'            => $pins,
                ],
                $reference
            );

            // 3. Clear Form Key
            session()->forget('education_form_key');

            // 4. Send Email Receipt
            try {
                Mail::to($user->email)->send(new TransactionReceiptMail($audit));
            } catch (\Throwable $e) {
                // Silent fail - don't block transaction
            }

        } catch (\Throwable $e) {
            // Check idempotency race condition
            if ($existing = WalletAudit::where('reference', $reference)->first()) {
                return redirect()->route('transactions.show', $existing);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('transactions.show', $audit)
            ->with('success', "✅ {$serviceName} purchase successful.")
            ->with('autoShare', true);
    }
}
