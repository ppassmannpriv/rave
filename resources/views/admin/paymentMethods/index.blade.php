@extends('layouts.admin')
@section('content')
<div class="card mb-5 border-bottom-0">
    <div class="card-header">
        <h1 class="h4 m-0">{{ trans('cruds.paymentMethods.title_singular') }} {{ trans('global.list') }}</h1>
    </div>
</div>

@foreach($paymentMethods as $paymentMethod)
    <div class="card">
        <div class="card-header">
            {{ $paymentMethod->name }}
        </div>

        <div class="card-body">
            <p>{{ $paymentMethod->description }}</p>
            <p>The configuration for this is done by your administrator. Contact him for further advice.</p>
            @include('admin.paymentMethods.' . $paymentMethod->alias)
        </div>
    </div>
@endforeach


@endsection
@section('scripts')
@parent
<script>
@endsection
