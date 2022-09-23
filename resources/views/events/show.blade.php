@extends('layouts.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    {!! $event->eventTicket !!}
                </div>

                <div class="card-body">
                    <!--<div class="alert alert-success" role="alert">
                        asdf
                    </div>-->
                    <form action="/cart/add/" method="POST">
                        @csrf
                        <input type="text" value="1" name="event_ticket_id"/>
                        <input type="text" value="1" name="qty"/>
                        <input type="submit" value="Submit" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
