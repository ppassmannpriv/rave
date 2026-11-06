<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Site\WebController;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserController extends WebController
{
    public function index(Request $request): Factory|View|Application
    {
        $user = \Auth::getUser();

        return $this->respond('user.index', ['user' => $user, 'siteType' => 'user']);
    }

    public function edit(): Factory|View|Application
    {
        $user = \Auth::getUser();

        return $this->respond('user.edit', ['user' => $user, 'siteType' => 'user']);
    }
}
