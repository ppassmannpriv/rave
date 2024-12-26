<?php

namespace App\Providers;

use App\Cart\CartManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(CartManager::class, fn ($app) => new CartManager(session()));
    }
}
