<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\AirtimeController;
use App\Http\Controllers\CableController;
use App\Http\Controllers\ElectricityController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\VTUController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PaymentController;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PricingController;

// ---------------------------------------------
// PUBLIC ROUTES
// ---------------------------------------------
Route::view('/', 'welcome');

// Theme Toggle
Route::post('/theme/toggle', function () {
    $settings = app(\App\Services\SettingsService::class);
    $newTheme = $settings->toggleTheme();
    config(['app.theme' => $newTheme]);

    return response()->json(['theme' => $newTheme]);
})->name('theme.toggle');

// ---------------------------------------------
// AUTHENTICATED USER ROUTES
// ---------------------------------------------
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ---------------- PROFILE ----------------
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::put('/profile/pin', [App\Http\Controllers\PinController::class, 'update'])
        ->name('profile.pin.update');

    // ---------------- VTU DASHBOARD ----------------
    Route::get('/vtu', [VTUController::class, 'index'])
        ->name('vtu.dashboard');

    Route::get('/transactions/receipt/{reference}', [TransactionController::class, 'receipt'])
        ->name('transactions.receipt');

    // ---------------- VTU PURCHASES (RATE LIMITED) ----------------
    Route::middleware('throttle:5,1')->group(function () {

        Route::get('/buy-data', [DataController::class, 'index'])
            ->name('data.index');

        Route::post('/buy-data', [DataController::class, 'purchase'])
            ->name('data.purchase');

        Route::get('/buy-airtime', [AirtimeController::class, 'index'])
            ->name('airtime.index');

        Route::post('/buy-airtime', [AirtimeController::class, 'purchase'])
            ->name('airtime.purchase');

        Route::get('/buy-cable', [CableController::class, 'index'])
            ->name('cable.index');

        Route::post('/buy-cable', [CableController::class, 'purchase'])
            ->name('cable.purchase');

        Route::get('/buy-electricity', [ElectricityController::class, 'index'])
            ->name('electricity.index');

        Route::post('/buy-electricity', [ElectricityController::class, 'purchase'])
            ->name('electricity.purchase');

        Route::get('/education', [EducationController::class, 'index'])
            ->name('education.index');

        Route::post('/education', [EducationController::class, 'purchase'])
            ->name('education.buy');
    });

    // ---------------- REFERRALS ----------------
    Route::get('/referral', [ReferralController::class, 'index'])
        ->name('referral.index');

    Route::post('/referral', [ReferralController::class, 'store'])
        ->name('referral.store');

    Route::post('/referral/withdraw', [ReferralController::class, 'withdraw'])
        ->name('referral.withdraw');

    // ---------------- WALLET FUNDING ----------------
    //
    // Portfolio demonstration:
    // Funding requests are handled through PaymentService
    // and the provider-neutral DemoPaymentGateway.
    //
    Route::get('/wallet/fund', [WalletController::class, 'showFundForm'])
        ->name('wallet.fund.form');

    Route::post('/wallet/fund', [WalletController::class, 'initiateFunding'])
        ->name('wallet.fund');

    Route::post('/payments/initiate', [WalletController::class, 'initiateFunding'])
        ->name('payments.initiate');

    Route::get('/payments/deposit', [PaymentController::class, 'showDepositForm'])
        ->name('payments.deposit');

    // ---------------- TRANSACTIONS ----------------
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');

    Route::get('/transactions/export/{format}', [TransactionController::class, 'export'])
        ->whereIn('format', ['csv', 'xlsx', 'pdf'])
        ->name('transactions.export');

    Route::get('/transactions/{audit}', [TransactionController::class, 'show'])
        ->name('transactions.show');

    // ---------------- ADMIN ROUTES ----------------
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['auth', 'can:is-admin'])
        ->group(function () {

            // -------- DASHBOARD & HEALTH --------
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->name('dashboard');

            Route::get('/system-health', [AdminDashboardController::class, 'systemHealth'])
                ->name('system.health');

            Route::get('/activity', [AdminDashboardController::class, 'activity'])
                ->name('activity');

            // -------- WEBHOOK MONITOR --------
            Route::get('/webhooks', [AdminDashboardController::class, 'webhookLogs'])
                ->name('webhooks');

            Route::post('/webhooks/{id}/retry', [AdminDashboardController::class, 'retryWebhook'])
                ->name('webhooks.retry');

            // -------- PAYMENTS --------
            Route::get('/payments/pending', [AdminDashboardController::class, 'pendingPayments'])
                ->name('payments.pending');

            Route::get('/payments/history', [AdminDashboardController::class, 'paymentHistory'])
                ->name('payments.history');

            Route::get('/payments/failed', [AdminDashboardController::class, 'failedPayments'])
                ->name('payments.failed');

            // -------- TRANSACTIONS --------
            Route::get('/transactions/{reference}/receipt', [TransactionController::class, 'receipt'])
                ->name('transactions.receipt');

            // -------- PRICING CONTROL --------
            Route::get('/pricing', [PricingController::class, 'index'])
                ->name('pricing');

            Route::put('/pricing/{id}', [PricingController::class, 'update'])
                ->name('pricing.update');

            Route::put('/pricing/airtime/{id}', [PricingController::class, 'updateAirtime'])
                ->name('pricing.airtime.update');
        });
});

// ---------------------------------------------
// AUTH SCAFFOLDING
// ---------------------------------------------
require __DIR__.'/auth.php';

// Prevent direct admin login URL
Route::get('/admin/login', fn () => redirect()->route('login'));