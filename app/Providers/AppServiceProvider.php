<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use App\Services\SettingsService;
use App\Models\User;
use App\Observers\UserObserver;
use App\Contracts\PaymentGatewayInterface;
use App\Gateways\DemoPaymentGateway;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);

        $this->app->bind(
            PaymentGatewayInterface::class,
            DemoPaymentGateway::class
        );
    }

    public function boot(): void
    {
        Gate::define('is-admin', fn ($user) => $user->is_admin == 1);

        // Force HTTPS in production only
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Register observers (LOGGING ONLY)
        // User::observe(UserObserver::class);

        // Load global settings safely
        if (Schema::hasTable('settings')) {
            try {
                config([
                    'app.theme' => app(SettingsService::class)->getTheme(),
                ]);
            } catch (\Throwable $e) {
                // Silent fail during boot
            }
        }
    }
}