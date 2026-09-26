<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\WalletService;

class EnsureUserWalletExists
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        app(WalletService::class)
            ->getOrCreateWalletFor($event->user);
    }
}
