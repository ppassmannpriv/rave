<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\EventTicketCode;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('order_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $orders = Order::with(['user', 'transaction'])->get();

        $users = User::get();

        $payments = Payment::get();

        return view('admin.orders.index', compact('orders', 'payments', 'users'));
    }

    public function create()
    {
        abort_if(Gate::denies('order_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $payments = Payment::pluck('reference', 'id')->prepend(trans('global.pleaseSelect'), '');

        $event_ticket_codes = EventTicketCode::pluck('code', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.orders.create', compact('event_ticket_codes', 'payments', 'users'));
    }

    public function store(StoreOrderRequest $request)
    {
        $order = Order::create($request->all());

        return redirect()->route('admin.orders.index');
    }

    public function edit(Order $order)
    {
        abort_if(Gate::denies('order_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $payments = Payment::pluck('reference', 'id')->prepend(trans('global.pleaseSelect'), '');

        $event_ticket_codes = EventTicketCode::pluck('code', 'id')->prepend(trans('global.pleaseSelect'), '');

        $order->load('user', 'payment', 'event_ticket_code');

        return view('admin.orders.edit', compact('event_ticket_codes', 'order', 'payments', 'users'));
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->all());

        return redirect()->route('admin.orders.index');
    }

    public function show(Order $order)
    {
        abort_if(Gate::denies('order_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $order->load('user', 'transaction', 'orderItems');

        return view('admin.orders.show', compact('order'));
    }

    public function destroy(Order $order)
    {
        abort_if(Gate::denies('order_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $order->delete();

        return back();
    }

    public function massDestroy(MassDestroyOrderRequest $request)
    {
        Order::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
