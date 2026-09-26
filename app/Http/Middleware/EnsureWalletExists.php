<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureWalletExists
{
    public function handle(Request $request, Closure $next)
    {
        // Wallet creation is now handled on LOGIN via event listener
        return $next($request);
    }
}
