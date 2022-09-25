@extends('layouts.app')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    {{ $event->name }}
                </div>

                <div class="card-body">
                    <span>Start: {{ $event->start }}</span>
                    <span>End: {{ $event->end }}</span>
                    <span>{{ $event->location }}</span>

                    <ul class="list-group">
                    @foreach($event->eventTickets as $eventTicket)
                        <li class="list-group-item">
                            <span>type: {{ $eventTicket->ticket_type }}</span>
                            <span>price: {{ $eventTicket->price }}</span>
                            <span>from: {{ $eventTicket->from }}</span>
                            <span>to: {{ $eventTicket->to }}</span>
                            <span>in_stock: {{ $eventTicket->stock }}</span>
                            <form action="/cart/add/" method="POST">
                                @csrf
                                <input type="hidden" value="{{ $eventTicket->id }}" name="event_ticket_id" />
                                <input type="hidden" value="1" name="qty-{{ $event->id }}" id="qty-{{ $event->id }}" />
                                <input type="submit" value="Add to Cart" />
                            </form>
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
