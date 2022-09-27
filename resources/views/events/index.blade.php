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
<li class="list-group-item flex-wrap d-flex">
    <div class="col-sm-6 col-lg-12 d-flex"><h4>{{ $event->name }}</h4></div>
    <div class="col-sm-6 col-lg-6 d-flex">{{ date('d-m-Y', strtotime($event->start)) }} - {{ date('d-m-Y', strtotime($event->end)) }}</div>
    <div class="col-sm-6 col-lg-6 d-flex"><a href="{{ route('events.show', ['id' => $event->id]) }}/">Details</a></div>
</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
