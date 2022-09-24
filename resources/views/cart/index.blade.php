
@extends('layouts.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    Cart
                </div>

                <div class="card-body">
                    <ul class="list-group">
                        @foreach($cart->cartItems as $cartItem)
                        <li class="list-group-item">
                            <span>type: {{ $cartItem->eventTicket->ticket_type }}</span>
                            <span>single price: {{ $cartItem->single_price }}</span>
                            <span>x {{ $cartItem->qty }}</span>
                            <span>row price: {{ $cartItem->row_price }}</span>
                            <a href="{!! route('cart.remove', ['id' => $cartItem->id]) !!}">Remove</a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
