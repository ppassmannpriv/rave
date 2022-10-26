
@extends('layouts.admin')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-4">
            @include('admin.partials.dashboard.nextEventStats')
        </div>
        <div class="col-lg-8">
            @include('admin.partials.dashboard.orderWidget')
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
