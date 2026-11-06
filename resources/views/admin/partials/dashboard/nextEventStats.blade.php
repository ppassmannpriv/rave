<div class="card">
    <div class="card-header">
        {{ trans('cruds.event.title') }}
    </div>

    <div class="card-body">
        <strong>{{ $event->name }}</strong>
        <p>
            Start: <strong>{{ $event->start }}</strong><br />
            End: <strong>{{ $event->end }}</strong><br />
            Location: <strong>{{ $event->location }}</strong><br />
            Tickets sold: <strong>{{ $event->eventTicketsSold() }}</strong><br />
            Estimated income: <strong>@money($event->eventTicketsSoldPrice())</strong>
        </p>
    </div>
</div>
