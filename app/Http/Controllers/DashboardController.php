<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\WalletService;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        /**
         * 1. ALWAYS use WalletService
         * Guarantees integrity + correct balances
         */
        $wallet = app(WalletService::class)
            ->getOrCreateWalletFor($user);

        /**
         * 2. Canonical wallet balance
         */
        $walletBalance = (float) $wallet->current_balance;

        /**
         * 3. Transactions
         */
        $totalTransactions = $wallet->audits()->count();

        $todaySpend = $wallet->audits()
            ->where('direction', 'debit')
            ->where('status', 'success')
            ->whereDate('created_at', now()->toDateString()) // ✅ FIX
            ->sum('amount');

        $recentTransactions = $wallet->audits()
            ->latest()
            ->limit(5)
            ->get();

        /**
         * 4. Referral Earnings (FIXED)
         * Include BOTH rewarded + paid
         */
        $referralEarnings = $user->referrals()
            ->whereIn('status', ['rewarded', 'paid']) // ✅ FIX
            ->sum('bonus_amount');

        /**
         * 5. Blade compatibility
         */
        $authUserWallet = $wallet;

        return view('vtu.dashboard', compact(
            'walletBalance',
            'totalTransactions',
            'todaySpend',
            'recentTransactions',
            'referralEarnings',
            'authUserWallet'
        ));
    }
}
