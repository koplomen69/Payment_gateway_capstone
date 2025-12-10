<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MidtransService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MidtransService::class, function ($app) {
            return new MidtransService();
        });
    }

    public function boot(): void
    {
        // Boot logic lain jika ada
    }
}
