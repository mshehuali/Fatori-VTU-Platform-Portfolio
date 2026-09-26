<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;

class WalletAudit extends Model
{
    use HasFactory;

    protected $table = 'wallet_audits';

    /**
     * Ledger must be append-only
     */
    protected $guarded = ['id'];

    protected $casts = [
        'amount'          => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
        'meta'            => 'array',
    ];

    /* -------------------------------------------------
     | Relationships
     |-------------------------------------------------*/
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* -------------------------------------------------
     | 🔖 DISPLAY HELPERS (NEW — SAFE)
     |-------------------------------------------------*/

    /**
     * Professional, user-facing reference
     * (Keeps internal UUID/reference untouched)
     */
    public function displayReference(): string
    {
        $prefixMap = [
            'funding'     => 'FUND',
            'airtime'     => 'AIR',
            'data'        => 'DATA',
            'cable'       => 'CAB',
            'electricity' => 'ELEC',
            'referral'    => 'REF',
        ];

        $prefix = $prefixMap[$this->source] ?? strtoupper(substr($this->source ?? 'TX', 0, 3));

        return sprintf(
            'FTS-%s-%s-%s',
            $prefix,
            optional($this->created_at)->format('Ymd'),
            substr($this->reference, -4)
        );
    }

    /**
     * Human-readable transaction type
     */
    public function displayType(): string
    {
        return ucfirst(str_replace('_', ' ', $this->source));
    }

    /* -------------------------------------------------
     | 🔐 MODEL LIFECYCLE PROTECTION
     |-------------------------------------------------*/
    protected static function booted()
    {
        /**
         * Auto-generate reference if missing
         */
        static::creating(function (WalletAudit $audit) {

            if (empty($audit->reference)) {
                $source = strtolower((string) ($audit->source ?? ''));

                $map = [
                    'funding'      => 'FUND',
                    'airtime'      => 'AIR',
                    'data'         => 'DATA',
                    'cable'        => 'CABL',
                    'electricity'  => 'ELEC',
                    'purchase'     => 'PUR',
                    'transfer_in'  => 'TRIN',
                    'transfer_out' => 'TROUT',
                    'referral'     => 'REF',
                ];

                $prefix = $map[$source] ?? 'VTU';

                $audit->reference = $prefix
                    . '-' . now()->format('YmdHis')
                    . '-' . random_int(1000, 9999);
            }
        });

        /**
         * Prevent UPDATE & DELETE on ledger
         */
        static::updating(function () {
            throw new Exception('Ledger entries are immutable');
        });

        static::deleting(function () {
            throw new Exception('Ledger entries cannot be deleted');
        });

        /**
         * Lightweight fraud signal
         */
        static::created(function (WalletAudit $tx) {

            if ($tx->direction !== 'debit') {
                return;
            }

            $burst = self::where('wallet_id', $tx->wallet_id)
                ->where('direction', 'debit')
                ->where('created_at', '>=', now()->subMinutes(3))
                ->count();

            if ($burst >= 4 && class_exists(\App\Services\FraudGuardService::class)) {
                \App\Services\FraudGuardService::log(
                    $tx->wallet_id,
                    'critical',
                    'debit_burst_activity',
                    $tx->reference,
                    ['count' => $burst]
                );
            }
        });
    }
}

