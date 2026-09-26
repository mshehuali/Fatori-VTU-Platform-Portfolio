<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referred_id',  // ✅ CRITICAL: Must be here to link the new user
        'friend_name',  // ✅ CRITICAL: Must be here to save the name
        'status',
        'bonus_amount',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}