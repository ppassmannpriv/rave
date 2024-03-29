@extends('layouts.checkout')
@section('content')
<div class="content">
    <div class="row m-0">
        <div class="col-lg-12 p-0">
            <div class="card rounded-0">
                <div class="card-header">
                    <h2 class="h4 m-0">Checkout</h2>
                </div>

                <div class="card-body">
                    <form method="post" action="{{ route('cart.order') }}">
                        @csrf
                        <fieldset>
                            <legend>Cart</legend>
                            @if($cart->cartItems->count() > 0)
                                <ul class="list-group container-fluid p-0 border border-bottom-0" id="cart-list">
                                    @foreach($cart->cartItems as $cartItem)
                                    <li class="list-group-item d-flex row border-bottom m-0">
                                        <input type="hidden" name="cart_item[]" value="{{ $cartItem->id }}" />
                                        <span class="d-flex name col col-md-6 col-sm-12">{{ $cartItem->eventTicket?->event?->name }}</span>
                                        @if($cartItem->type === 'FEE')
                                            <span class="d-flex type col col-md-2 col-sm-12 text-right">Transaction Fee</span>
                                        @else
                                            <span class="d-flex type col col-md-2 col-sm-12 text-right">{{ $cartItem->eventTicket ? $cartItem->eventTicket::TICKET_TYPE_RADIO[$cartItem->eventTicket?->ticket_type] : null }}</span>
                                        @endif
                                        <span class="d-flex qty col col-md-1 col-sm-12 text-right">x {{ $cartItem->qty }}</span>
                                        <span class="d-flex price col col-md-2 col-sm-12 text-right">@money($cartItem->row_price)</span>
                                        @if($cartItem->type !== 'FEE')
                                            <a class="d-flex remove col col-md-1 text-right" href="{!! route('cart.remove', ['id' => $cartItem->id]) !!}">Remove</a>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <p>There is nothing in your cart.</p>
                            @endif
                        </fieldset>
                        @if($cart->cartItems->count() > 0)
                            <style>
                                /* fuck coreUI and fuck flexbox. */;
                                @media screen and (max-width: 600px) {
                                    .checkout .force-sm-block {
                                        display: block !important;
                                        float: left;
                                        clear: both;
                                    }
                                }
                            </style>
                            <div class="form-group mt-5">
                                @include('partials.checkout.billing-address')
                                <div class="d-flex container-fluid p-0 row m-0">
                                    <div class="d-flex col-md-3 col-sm-12 p-0">
                                        @include('partials.checkout.payment-method')
                                    </div>
                                    <ul id="totals" class="list-group container-fluid p-0 border col-md-3 col-sm-12 padding-left-0 justify-content-center" style="display: block !important">
                                        <li class="list-group-item d-flex">
                                            <span class="d-flex price col-sm-8 text-left">Subtotal</span>
                                            <strong class="d-flex price col-sm-4 text-right">@money($cart->getSubTotal())</strong>
                                        </li>
                                        <li class="list-group-item d-flex">
                                            <span class="d-flex price col-sm-8 text-left">Transaction Fees</span>
                                            <strong class="d-flex price col-sm-4 text-right">@money($cart->getPayPalCost())</strong>
                                        </li>
                                        <li class="list-group-item d-flex">
                                            <span class="d-flex price col-sm-8 text-left">Total</span>
                                            <strong class="d-flex price col-sm-4 text-right">@money($cart->getTotal())</strong>
                                        </li>
                                    </ul>
                                    <div class="col-md-4 col-sm-12 p-0 mt-3">
                                        <ul id="legal-agreements" class="list-group container-fluid border col-sm-12 p-0">
                                            <li class="list-group-item d-flex ml-4">
                                                <input id="general-terms-and-conditions" name="general-terms-and-conditions" type="checkbox" required value="1" class="form-check-input">
                                                <label for="general-terms-and-conditions" class="form-check-label">I accept the general terms and conditions.</label>
                                            </li>
                                            <li class="list-group-item d-flex ml-4">
                                                <input id="data-protection-and-revocation" name="data-protection-and-revocation" type="checkbox" required value="1" class="form-check-input">
                                                <label for="data-protection-and-revocation" class="form-check-label">I have been informed about the data protection declaration and revocation information.</label>
                                            </li>
                                        </ul>
                                        <button type="submit" class="btn btn-success col-sm-12 text-center mt-4">Order</button>
                                    </div>
                                </div>
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
