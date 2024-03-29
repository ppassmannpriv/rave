<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Bookkeeping\GetIncomePerPaymentMethod;
use App\Actions\Order\AbortOrderAction;
use App\Actions\Order\CancelOrderAction;
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

        $order->load('user', 'transaction', 'orderItems');

        $orderStatuses = [
            Order::STATUS_INITIALIZED,
            Order::STATUS_PROCESSING,
            Order::STATUS_PAID,
            Order::STATUS_CLOSED,
            Order::STATUS_CANCELLED,
        ];

        return view('admin.orders.edit', compact('order', 'users', 'orderStatuses'));
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

    public function cancel(int $orderId): \Illuminate\Http\RedirectResponse
    {
        abort_if(Gate::denies('order_cancel'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $order = Order::find($orderId);
        abort_if(!$order->isCancellable(), Response::HTTP_BAD_REQUEST, '400 Bad Request');
        CancelOrderAction::make()->handle($order);

        return redirect()->route('admin.orders.index');
    }

    public function abort(int $orderId): \Illuminate\Http\RedirectResponse
    {
        abort_if(Gate::denies('order_abort'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $order = Order::find($orderId);
        abort_if(!$order->isCancellable(), Response::HTTP_BAD_REQUEST, '400 Bad Request');
        AbortOrderAction::make()->handle($order);

        return redirect()->route('admin.orders.index');
    }
}
