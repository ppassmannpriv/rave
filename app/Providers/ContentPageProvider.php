<?php

namespace App\Providers;

use App\Contracts\Cart;
use App\Contracts\Navigation;
use App\Services\CartService;
use App\Services\NavigationService;
use Illuminate\Support\ServiceProvider;

class ContentPageProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Navigation::class, fn ($app) => new NavigationService());
    }

    public function boot()
    {
        $this->app->singleton(Cart::class, fn ($app) => new CartService(
            session()
        ));
    }
}
