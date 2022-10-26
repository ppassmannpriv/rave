<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;

class HomeController
{
    public function index()
    {
        $orders = Order::where('deleted_at', '=', null)->orderByDesc('created_at')->limit(5)->get();
        return view('home', ['orders' => $orders]);
    }
}
