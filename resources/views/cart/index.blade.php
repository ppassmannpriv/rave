
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
                            <span>{{ $cartItem->ticket_type }}</span>
                            <span>{{ $cartItem->price }}</span>
                            <a href="{{ route('cart.remove', $cartItem->id) }}">Remove</a>
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
