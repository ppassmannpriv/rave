<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventTicketRequest;
use App\Http\Requests\UpdateEventTicketRequest;
use App\Http\Resources\Admin\EventTicketResource;
use App\Models\EventTicket;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventTicketsApiController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('event_ticket_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new EventTicketResource(EventTicket::with(['event'])->get());
    }

    public function store(StoreEventTicketRequest $request)
    {
        $eventTicket = EventTicket::create($request->all());

        return (new EventTicketResource($eventTicket))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(EventTicket $eventTicket)
    {
        abort_if(Gate::denies('event_ticket_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new EventTicketResource($eventTicket->load(['event']));
    }

    public function update(UpdateEventTicketRequest $request, EventTicket $eventTicket)
    {
        $eventTicket->update($request->all());

        return (new EventTicketResource($eventTicket))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(EventTicket $eventTicket)
    {
        abort_if(Gate::denies('event_ticket_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $eventTicket->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}