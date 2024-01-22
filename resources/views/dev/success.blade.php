@extends('layouts.checkout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h4 m-0">Checkout - Success</h2>
                </div>

                <div class="card-body">
                    <p>Thank you very much! We will contact you via email very soon!</p>
                    <fieldset>
                        <legend class="h4">Order #{{ $order->id }}</legend>
                    </fieldset>
                    <div class="row">
                        <div class="col-sm-12 col-lg-6 user-details">
                            @include('partials.checkout.success.userDetails')
                        </div>
                        <div class="col-sm-12 col-lg-6 transaction-details">
                            @include('partials.checkout.success.paymentMethod')
                        </div>
                    </div>
                    @include('partials.checkout.success.orderItems')
                    @include('partials.checkout.success.totals')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
