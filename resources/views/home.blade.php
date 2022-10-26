
@extends('layouts.admin')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    Dashboard
                </div>

                <div class="card-body">
                    @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif

                    You are logged in!
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @include('admin.partials.dashboard.nextEventStats')
        </div>
        <div class="col-lg-12">
            @include('admin.partials.dashboard.orderWidget')
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
