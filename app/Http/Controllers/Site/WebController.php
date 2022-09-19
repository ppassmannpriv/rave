<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Contracts\Navigation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class WebController extends Controller
{
    public function __construct(public Navigation $navigationService) {}

    public function respond(string $view, array $parameters = [], array $mergeData = []): Factory|View|Application
    {
        $parameters['isActive'] = $this->navigationService->isActive();
        return view($view, $parameters, $mergeData);
    }
}
