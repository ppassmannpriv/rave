<?php

namespace App\Providers;

use App\Contracts\Cart;
use App\Services\CartService;
use Illuminate\Support\ServiceProvider;
use App\Services\NavigationService;
use App\Contracts\Navigation;

class ContentPageProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Navigation::class, fn ($app) => new NavigationService());
    }

    public function boot() {
        $this->app->singleton(Cart::class, fn ($app) => new CartService(
            session()
        ));
    }
}
