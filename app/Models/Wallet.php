<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_balance',
        'ledger_balance',
        'status',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'ledger_balance'  => 'decimal:2',
    ];

    /**
     * The user who owns the wallet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Financial audit entries associated with the wallet.
     */
    public function audits()
    {
        return $this->hasMany(WalletAudit::class);
    }
}