<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookFailure extends Model
{
    protected $fillable = [
        'reference',
        'gateway',
        'payload',
        'retries',
    ];

    protected $casts = [
        'payload' => 'array',
        'retries' => 'integer',
    ];
}