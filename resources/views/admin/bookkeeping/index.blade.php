@extends('layouts.admin')
@section('content')
    <div class="card mb-5 border-bottom-0">
        <div class="card-header">
            <h1 class="h4 m-0">{{ trans('cruds.bookkeeping.title_singular') }} {{ trans('global.list') }}</h1>
        </div>
    </div>

    @foreach($bookkeeping as $eventStats)
        <div class="card">
            <div class="card-header">
                {{ $eventStats['event']->name }}
            </div>

            <div class="card-body">
                @foreach($eventStats['incomeSums'] as $paymentMethodName => $incomeSum)
                    <p>{{ $paymentMethodName }}: @money($incomeSum)</p>
                @endforeach
            </div>
        </div>
    @endforeach


@endsection
@section('scripts')
    @parent
    <script>
@endsection
