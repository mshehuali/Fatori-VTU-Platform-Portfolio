<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RetryWebhookPayment;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletAudit;
use App\Models\WebhookEvent;
use App\Models\WebhookFailure;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'processed' => WebhookEvent::count(),
            'failed' => WebhookFailure::count(),
            'retries' => WebhookFailure::where('retries', '>', 0)->count(),
        ];

        $walletStats = [
            'total' => Wallet::count(),
        ];

        $financials = [
            'total_revenue' => WalletAudit::where('direction', 'debit')
                ->where('status', 'success')
                ->sum('amount'),
            'net_profit' => 0,
            'pending_payouts' => 0,
        ];

        $userStats = [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', Carbon::today())->count(),
        ];

        $failedTransactions = WalletAudit::where('status', 'failed')
            ->latest()
            ->take(5)
            ->get();

        $recent = WalletAudit::with('user')
            ->latest()
            ->paginate(10);

        return view('admin.dashboard', compact(
            'stats',
            'walletStats',
            'recent',
            'financials',
            'userStats',
            'failedTransactions'
        ));
    }

    public function systemHealth()
    {
        return response()->json([
            'status' => 'ok',
            'database' => 'connected',
            'queue' => config('queue.default'),
        ]);
    }

    public function activity()
    {
        $activities = WalletAudit::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.activity', compact('activities'));
    }

    public function webhookLogs()
    {
        $events = WebhookEvent::latest()->limit(50)->get();
        $failures = WebhookFailure::latest()->get();

        return view('admin.webhooks', compact('events', 'failures'));
    }

    public function retryWebhook(string $id)
    {
        $failure = WebhookFailure::findOrFail($id);

        RetryWebhookPayment::dispatch($failure->reference);

        return back()->with('success', 'Webhook retry queued.');
    }

    public function pendingPayments()
    {
        $payments = PaymentRequest::with('user.wallet')
            ->whereIn('status', ['initiated', 'pending'])
            ->latest()
            ->paginate(20);

        return view('admin.payments.pending', compact('payments'));
    }

    public function paymentHistory()
    {
        $payments = PaymentRequest::where('status', 'processed')
            ->latest()
            ->paginate(20);

        return view('admin.payments.history', compact('payments'));
    }

    public function failedPayments()
    {
        $payments = PaymentRequest::whereIn('status', [
            'failed',
            'amount_mismatch',
            'gateway_mismatch',
        ])
            ->latest()
            ->paginate(20);

        return view('admin.payments.failed', compact('payments'));
    }
}
