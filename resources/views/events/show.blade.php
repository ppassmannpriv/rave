@extends('layouts.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-6 col-xs-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h4 m-0">{{ $event->name }}</h2>
                </div>

                <div class="card-body">
                    <span>Start: {{ date('d-m-Y H:i', strtotime($event->start)) }}</span><br />
                    <span>End: {{ date('d-m-Y H:i', strtotime($event->end)) }}</span><br />
                    <span>{{ $event->location }}</span>
                    {!! $event->description !!}
                    <h4 class="mt-4 border-top pt-4">Tickets</h4>
                    <ul class="list-group d-flex mt-4 border">
                    @foreach($event->eventTickets as $eventTicket)
                        <li class="list-group-item d-flex flex-wrap">
                            <div class="col-lg-3 col-sm-6 d-flex align-content-center flex-wrap"><span>{{ $eventTicket::TICKET_TYPE_RADIO[$eventTicket->ticket_type] }}</span></div>
                            <div class="col-lg-3 col-sm-6 d-flex align-content-center flex-wrap"><span>@money($eventTicket->price)</span></div>
                            <div class="col-sm-12 d-md-none d-flex flex-wrap mt-3"></div>
                            @if($eventTicket->isAvailable())
                                <div class="col-lg-6 col-sm-12 d-flex align-content-center flex-wrap">
                                    <form action="/cart/add/" method="POST" class="w-100">
                                        @csrf
                                        <input type="hidden" value="{{ $eventTicket->id }}" name="event_ticket_id" />
                                        <input type="number" min="1" max="4" class="form-control w-50 float-left rounded-0" value="1" name="qty" id="qty-{{ $event->id }}" />
                                        <input type="submit" value="Buy" class="btn btn-outline-success w-50 float-end rounded-0"/>
                                    </form>
                                </div>
                            @else
                                <div class="col-lg-6 col-sm-12 d-flex align-content-center flex-wrap">
                                    <strong>Sold out</strong>
                                </div>
                            @endif
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent

@endsection
