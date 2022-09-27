<?php

namespace App\Providers;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // @TODO: replace this with a proper phpmoney solution, as soon as you get to real payment providers.
        \Blade::directive('money', function ($money) {
            return env('CURRENCY_SYMBOL') . "<?php echo number_format($money, 2); ?>";
        });
    }
}
