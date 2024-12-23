<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyEventTicketRequest;
use App\Http\Requests\StoreEventTicketRequest;
use App\Http\Requests\UpdateEventTicketRequest;
use App\Models\Event;
use App\Models\EventTicket;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventTicketsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('event_ticket_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $eventTickets = EventTicket::with(['event'])->get();

        $events = Event::get();

        return view('admin.eventTickets.index', compact('eventTickets', 'events'));
    }

    public function create()
    {
        abort_if(Gate::denies('event_ticket_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.eventTickets.create', compact('events'));
    }

    public function store(StoreEventTicketRequest $request)
    {
        $eventTicket = EventTicket::create($request->all());

        return redirect()->route('admin.event-tickets.index');
    }

    public function edit(EventTicket $eventTicket)
    {
        abort_if(Gate::denies('event_ticket_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $eventTicket->load('event');

        return view('admin.eventTickets.edit', compact('eventTicket', 'events'));
    }

    public function update(UpdateEventTicketRequest $request, EventTicket $eventTicket)
    {
        $eventTicket->update($request->all());

        return redirect()->route('admin.event-tickets.index');
    }

    public function show(EventTicket $eventTicket)
    {
        abort_if(Gate::denies('event_ticket_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $eventTicket->load('event');

        return view('admin.eventTickets.show', compact('eventTicket'));
    }

    public function destroy(EventTicket $eventTicket)
    {
        abort_if(Gate::denies('event_ticket_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $eventTicket->delete();

        return back();
    }

    public function massDestroy(MassDestroyEventTicketRequest $request)
    {
        EventTicket::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
