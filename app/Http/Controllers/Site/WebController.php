<?php

namespace App\Http\Controllers\Site;

use App\Actions\Cart\AddTicketToCart;
use App\Http\Controllers\Controller;
use App\Contracts\Navigation;
use App\Contracts\Cart;
use App\Models\EventTicket;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class WebController extends Controller
{
    public function __construct(public Navigation $navigationService, public Cart $cartService) {}

    public function respond(string $view, array $parameters = [], array $mergeData = []): Factory|View|Application
    {
        // AddTicketToCart::make()->handle(EventTicket::find(1));
        $parameters['pages'] = $this->navigationService->getPages();
        $parameters['cart'] = $this->cartService->content();

        return view($view, $parameters, $mergeData);
    }
}
