<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirtimeControl extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     * We must whitelist 'discount_percent' and 'is_active' to allow updates.
     */
    protected $fillable = [
        'network',
        'discount_percent', // ✅ Fixes the MassAssignmentException
        'is_active',
    ];

    /**
     * Type casting for better data handling
     */
    protected $casts = [
        'discount_percent' => 'decimal:2',
        'is_active'        => 'boolean',
    ];
}