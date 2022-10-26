<div class="card">
    <div class="card-header">
        {{ trans('cruds.event.title') }}
    </div>

    <div class="card-body">
        <h4>{{ $event->name }}</h4>
        <p>
            Start: <strong>{{ $event->start }}</strong><br />
            End: <strong>{{ $event->end }}</strong><br />
            Location: <strong>{{ $event->location }}</strong>
            Tickets sold: <strong>{{ $event->eventTicketsSold() }}</strong>
        </p>
    </div>
</div>
