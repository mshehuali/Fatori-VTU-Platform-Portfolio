<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPlan extends Model
{
    use HasFactory;

   
    protected $fillable = [
        'network',
        'name',
        'plan_id',
        'cost_price',
        'price',      
        'is_active',  
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'price'      => 'decimal:2',
        'is_active'  => 'boolean',
    ];
}