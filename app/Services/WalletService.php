<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletAudit;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class WalletService
{
    /**
     * Get the user's wallet or create it if it does not exist.
     */
    public function getOrCreateWalletFor(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_balance' => 0,
                'ledger_balance'  => 0,
                'status'          => 'active',
            ]
        );
    }

    /**
     * Credit a wallet.
     *
     * The reference acts as an idempotency key. A repeated credit
     * with the same reference returns the existing audit record
     * without changing the balance again.
     */
    public function credit(
        User|int $user,
        float|int $amount,
        string $source,
        string $description,
        array $meta = [],
        ?string $reference = null
    ): WalletAudit {
        $amount = round((float) $amount, 2);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Invalid credit amount.');
        }

        $userId = $user instanceof User ? $user->id : (int) $user;
        $reference ??= $this->generateReference(strtoupper($source));

        return DB::transaction(function () use (
            $userId,
            $amount,
            $source,
            $description,
            $meta,
            $reference
        ) {
            /*
             * Check idempotency before changing the wallet.
             *
             * This ordering is important: a duplicate reference
             * must never mutate the wallet balance.
             */
            if ($existing = WalletAudit::where('reference', $reference)->first()) {
                return $existing;
            }

            $wallet = Wallet::where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->status !== 'active') {
                throw new RuntimeException('Wallet is not active.');
            }

            $before = (float) $wallet->current_balance;
            $after  = round($before + $amount, 2);

            $wallet->update([
                'current_balance' => $after,
                'ledger_balance'  => round(
                    (float) $wallet->ledger_balance + $amount,
                    2
                ),
            ]);

            return $wallet->audits()->create([
                'wallet_id'      => $wallet->id,
                'user_id'        => $userId,
                'direction'      => 'credit',
                'source'         => $source,
                'description'    => $description,
                'reference'      => $reference,
                'amount'         => $amount,
                'balance_before' => $before,
                'balance_after'  => $after,
                'status'         => 'success',
                'meta'           => $meta,
            ]);
        }, 5);
    }

    /**
     * Debit a wallet.
     *
     * Uses a database transaction and row-level locking to prevent
     * concurrent requests from spending the same balance.
     */
    public function debit(
        User|int $user,
        float|int $amount,
        string $source,
        string $description,
        array $meta = [],
        ?string $reference = null
    ): WalletAudit {
        $amount = round((float) $amount, 2);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Invalid debit amount.');
        }

        $userId = $user instanceof User ? $user->id : (int) $user;
        $reference ??= $this->generateReference(strtoupper($source));

        return DB::transaction(function () use (
            $userId,
            $amount,
            $source,
            $description,
            $meta,
            $reference
        ) {
            if ($existing = WalletAudit::where('reference', $reference)->first()) {
                return $existing;
            }

            $wallet = Wallet::where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->status !== 'active') {
                throw new RuntimeException('Wallet is not active.');
            }

            if ((float) $wallet->current_balance < $amount) {
                throw new RuntimeException('Insufficient balance.');
            }

            $before = (float) $wallet->current_balance;
            $after  = round($before - $amount, 2);

            $wallet->update([
                'current_balance' => $after,
                'ledger_balance'  => round(
                    (float) $wallet->ledger_balance - $amount,
                    2
                ),
            ]);

            return $wallet->audits()->create([
                'wallet_id'      => $wallet->id,
                'user_id'        => $userId,
                'direction'      => 'debit',
                'source'         => $source,
                'description'    => $description,
                'reference'      => $reference,
                'amount'         => $amount,
                'balance_before' => $before,
                'balance_after'  => $after,
                'status'         => 'success',
                'meta'           => $meta,
            ]);
        }, 5);
    }

    /**
     * Process a VTU purchase from the wallet.
     *
     * The core wallet operation remains provider-neutral.
     */
    public function processVtuPurchase(
        User|int $user,
        float|int $amount,
        string $type = 'AIRTIME',
        array $meta = []
    ): WalletAudit {
        $source = strtolower($type);
        $description = 'Purchase of ' . ucfirst($source);

        return $this->debit(
            $user,
            $amount,
            $source,
            $description,
            array_merge($meta, [
                'vtu_type' => $type,
            ])
        );
    }

    /**
     * Debit a purchase amount plus a supplied fee.
     */
    public function debitWithFee(
        User|int $user,
        float|int $coreAmount,
        string $source,
        string $description,
        array $meta = [],
        float|int $fee = 0
    ): WalletAudit {
        $coreAmount = round((float) $coreAmount, 2);
        $fee = round((float) $fee, 2);

        if ($coreAmount <= 0) {
            throw new InvalidArgumentException('Invalid transaction amount.');
        }

        if ($fee < 0) {
            throw new InvalidArgumentException('Invalid fee.');
        }

        return $this->debit(
            $user,
            round($coreAmount + $fee, 2),
            $source,
            $description,
            array_merge($meta, [
                'core_amount' => $coreAmount,
                'fee'         => $fee,
            ])
        );
    }

    /**
     * Generate a unique application reference.
     */
    public function generateReference(string $prefix = 'REF'): string
    {
        return $prefix . '-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
    }
}