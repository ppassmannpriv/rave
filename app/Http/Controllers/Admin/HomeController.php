<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response;
use Gate;

class HomeController
{
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        abort_if(Gate::denies('admin_panel_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $orders = Order::where('deleted_at', '=', null)->orderByDesc('created_at')->limit(5)->get();
        $event = Event::where('end', '>', date('Y-m-d H:i:s'))->orderByDesc('created_at')->first();

        return view('home', [
            'orders' => $orders,
            'event' => $event
        ]);
    }
}
