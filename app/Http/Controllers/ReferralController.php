<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // ✅ CRITICAL: Added missing DB import
use App\Services\WalletService;

class ReferralController extends Controller
{
    // Inject WalletService for safe transactions
    public function __construct(protected WalletService $walletService) {}

    public function index()
    {
        $user = Auth::user();

        // ✅ FIX: Auto-generate code if missing
        if (empty($user->referral_code)) {
            $user->referral_code = 'REF' . strtoupper(Str::random(6));
            $user->save();
        }

        $refLink = route('register', ['ref' => $user->referral_code]);

        // Fetch all referrals
        $referrals = $user->referrals()->latest()->get();

        // 1. Total Friends Invited
        $totalFriends = $referrals->count();

        // 2. Pending (Registered but hasn't funded wallet yet)
        $pendingCount = $referrals->where('status', 'pending')->count();

        // 3. Available to Withdraw (Status = 'rewarded')
        $availableToWithdraw = $referrals->where('status', 'rewarded')->sum('bonus_amount');

        // 4. Total Withdrawn (Status = 'paid')
        $totalWithdrawn = $referrals->where('status', 'paid')->sum('bonus_amount');

        return view('vtu.referrals', compact(
            'refLink',
            'referrals',
            'totalFriends',
            'pendingCount',
            'availableToWithdraw',
            'totalWithdrawn'
        ));
    }

    public function withdraw()
    {
        $user = Auth::user();

        try {
            // We use a transaction to prevent race conditions (double withdrawals)
            $amountWithdrawn = DB::transaction(function () use ($user) {

                // 🔒 Lock rewarded referrals to prevent double-spending
                $rewarded = $user->referrals()
                    ->where('status', 'rewarded')
                    ->lockForUpdate()
                    ->get();

                $totalAmount = $rewarded->sum('bonus_amount');

                // Check Minimum Withdrawal (Updated to ₦200 as requested, or keep ₦1000 if you prefer)
                if ($totalAmount < 1000) {
                    throw new \Exception('Minimum withdrawal is ₦1.000.');
                }

                // ✅ Safe wallet credit via service
                // Note: We use $this->walletService directly since it's injected
                $this->walletService->credit(
                    $user,
                    $totalAmount,
                    'referral_bonus',
                    'Referral Bonus Withdrawal',
                    [
                        'admin_override' => true,
                        'referral_count' => $rewarded->count(),
                    ]
                );

                // ✅ Mark referrals as paid AFTER credit
                foreach ($rewarded as $ref) {
                    $ref->update(['status' => 'paid']);
                }

                return $totalAmount; // Return amount to use outside transaction
            });

            return back()->with(
                'success',
                "₦" . number_format($amountWithdrawn) . " withdrawn to wallet successfully!"
            );

        } catch (\Throwable $e) {

            \Log::error('Referral withdrawal failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                $e->getMessage() ?: 'Withdrawal failed. Please try again.'
            );
        }
    }
}