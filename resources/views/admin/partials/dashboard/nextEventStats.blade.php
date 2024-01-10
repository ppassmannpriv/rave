<div class="card">
    <div class="card-header">
        {{ trans('cruds.event.title') }}
    </div>

    <div class="card-body">
        @foreach($events as $event)
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr><th colspan=2><h4 class="text-center">{{ $event->name }}</h4></th></tr>
            </thead>
            <tbody>
            <tr><td>Start</td><td><strong>{{ $event->start }}</strong></td></tr>
            <tr><td>End</td><td><strong>{{ $event->end }}</strong></td></tr>
            <tr><td>Location</td><td><strong>{{ $event->location }}</strong></td></tr>
            @foreach ($event->eventTickets as $eventTicket)
                <tr><td colspan=2></td></tr>
                <tr><td>Ticket type</td><td><strong>{{ $eventTicket->getType() }}</strong></td></tr>
                <tr><td>Sale from</td><td><strong>{{ $eventTicket->from }}</strong></td></tr>
                <tr><td>Sale to</td><td><strong>{{ $eventTicket->to }}</strong></td></tr>
            @endforeach
            <tr><td colspan=2></td></tr>
            <tr><td>Tickets reserved</td><td><strong>{{ $event->eventTicketsReserved() }}</strong></td></tr>
            <tr><td>Tickets sold</td><td><strong>{{ $event->eventTicketsSold() }}</strong></td></tr>
            <tr><td>Estimated income</td><td><strong>@money($event->eventTicketsSoldPrice())</strong></td></tr>
            </tbody>
        </table>
        @endforeach
    </div>
</div>