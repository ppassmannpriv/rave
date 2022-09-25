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
                    <pre>
                        {{ $order }}
                    </pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
