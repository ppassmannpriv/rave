@extends('layouts.checkout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 m-0">Checkout</h1>
                </div>

                <div class="card-body">
                    <form method="post" action="{{ route('cart.order') }}">
                        @csrf
                        <fieldset>
                            <legend>Cart</legend>
                            @if($cart->cartItems->count() > 0)
                                <ul class="list-group container" id="cart-list">
                                    @foreach($cart->cartItems as $cartItem)
                                    <li class="list-group-item d-flex">
                                        <input type="hidden" name="cart_item" value="{{ $cartItem->id }}" />
                                        <span class="d-flex name col-sm-6">{{ $cartItem->eventTicket->event->name }}</span>
                                        <span class="d-flex type col-sm-2 text-right">{{ $cartItem->eventTicket->ticket_type }}</span>
                                        <span class="d-flex qty col-sm-1 text-right">x {{ $cartItem->qty }}</span>
                                        <span class="d-flex price col-sm-2 text-right">{{ $cartItem->row_price }} EUR</span>
                                        <a class="d-flex remove col-sm-1 text-right" href="{!! route('cart.remove', ['id' => $cartItem->id]) !!}">Remove</a>
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <p>There is nothing in your cart.</p>
                            @endif
                        </fieldset>
                        @if($cart->cartItems->count() > 0)
                            <div class="form-group mt-5">
                                @include('partials.checkout.billing-address')
                                @include('partials.checkout.payment-method')
                                <button type="submit" class="btn btn-success">Order</button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
