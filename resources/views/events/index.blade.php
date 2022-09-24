@extends('layouts.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    Events
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <ul class="list-group">
                                @foreach($events as $event)
<li class="list-group-item">{{ $event->name }} <a href="{{ route('events.show', ['id' => $event->id]) }}/">Details</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <!--<div class="alert alert-success" role="alert">
                        asdf
                    </div>-->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
