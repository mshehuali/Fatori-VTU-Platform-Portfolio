<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'network',
        'category',
        'code',
        'display_name',
        'buy_price',
        'sell_price',
        'reseller_price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
