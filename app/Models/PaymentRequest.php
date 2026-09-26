<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PaymentRequest extends Model
{
    use HasFactory;

    protected $table = 'payment_requests';

    protected $fillable = [
        'reference',
        'user_id',
        'amount',
        'currency',
        'payment_method',   // ✅ ADD THIS LINE
        'status',
        'gateway_id',
        'gateway_response',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * The user who initiated the payment request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
