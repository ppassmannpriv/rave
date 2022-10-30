@extends('layouts.admin')
@section('content')
<div class="content">
    <div class="row">
        @if ($event !== null)
            <div class="col-lg-4">
                @include('admin.partials.dashboard.nextEventStats')
            </div>
        @endif
        <div class="col-lg-8">
            @include('admin.partials.dashboard.orderWidget')
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
