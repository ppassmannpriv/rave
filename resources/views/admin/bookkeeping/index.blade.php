@extends('layouts.admin')
@section('content')
    <div class="card mb-5 border-bottom-0">
        <div class="card-header">
            <h1 class="h4 m-0">{{ trans('cruds.bookkeeping.title_singular') }} {{ trans('global.list') }}</h1>
        </div>
    </div>
    <div class="container-fluid row">
        @foreach($bookkeeping as $i => $eventStats)
            <div class="card col col-5">
                <div class="card-header">
                    {{ $eventStats['event']->name }}
                </div>

                <div class="card-body">
                    @foreach($eventStats['incomeSums'] as $paymentMethodName => $incomeSum)
                        <p>{{ $paymentMethodName }}: <strong>@money($incomeSum)</strong></p>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

@endsection
@section('scripts')
    @parent
    <script>
@endsection
