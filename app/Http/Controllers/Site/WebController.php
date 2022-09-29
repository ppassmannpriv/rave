<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Contracts\Navigation;
use App\Contracts\Cart;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class WebController extends Controller
{
    public function __construct(public Navigation $navigationService, public Cart $cartService) {}

    public function respond(string $view, array $parameters = [], array $mergeData = []): Factory|View|Application
    {
        try {
            $parameters['pages'] = $this->navigationService->getPages();
            $parameters['cart'] = $this->cartService->content();
        } catch (\Throwable $throwable) {
            report($throwable);
        }

        return view($view, $parameters, $mergeData);
    }
}
