<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;

class WebController extends Controller
{
    public function respond(string $view, array $parameters = [], array $mergeData = [])
    {
        $parameters['isActive'] = request()->routeIs(request()->route()->getName());
        return view($view, $parameters, $mergeData);
    }
}
