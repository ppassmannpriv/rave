@extends('layouts.checkout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 m-0">Checkout - Success</h1>
                </div>

                <div class="card-body">
                    <p>Thank you very much! We will contact you via email very soon!</p>
                    <fieldset>
                        <legend class="h4">Order #{{ $order->id }}</legend>
                    </fieldset>
                    <div class="row">
                    <div class="col-sm-12 col-lg-6 user-details">
                        <h5 class="h5">Your details</h5>
                        <p>{{ $order->user->name }}<br />{{ $order->user->email }}</p>
                    </div>
                    <div class="col-sm-12 col-lg-6 transaction-details">
                        <h5 class="h5">Your payment</h5>
                        <p>{{ $order->transaction->paymentMethod->name }}</p>
                    </div>
                    </div>
                    <div class="orderItems">
                        <ul class="list-group container m-0 p-0" id="cart-list">
                            @foreach($order->orderItems as $orderItem)
                            <li class="list-group-item d-flex">
                                <div class="d-flex name col-sm-6 flex-fill"><span>{{ $orderItem->eventTicket->event->name }}</span></div>
                                <div class="d-flex type col-sm-2 text-right flex-fill"><span>{{ $orderItem->eventTicket::TICKET_TYPE_RADIO[$orderItem->eventTicket->ticket_type] }}</span></div>
                                <div class="d-flex qty col-sm-2 text-right flex-fill"><span class="w-100">x {{ $orderItem->qty }}</span></div>
                                <div class="d-flex price col-sm-2 text-right flex-fill"><span class="w-100">@money($orderItem->row_price)</span></div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="d-flex w-100 col-sm-12 col-lg-6 offset-lg-6 mt-2 text-end" id="totals">
                        <div id="total-sum" class="w-100 d-flex">
                            <span class="h4 w-100 text-right font-weight-normal">Your total <strong>@money($order->price)</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
