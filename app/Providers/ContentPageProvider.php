<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NavigationService;
use App\Contracts\Navigation;

class ContentPageProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Navigation::class, fn ($app) => new NavigationService());
    }
}
