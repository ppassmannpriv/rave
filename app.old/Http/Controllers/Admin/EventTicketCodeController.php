<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyEventTicketCodeRequest;
use App\Http\Requests\StoreEventTicketCodeRequest;
use App\Http\Requests\UpdateEventTicketCodeRequest;
use App\Models\EventTicketCode;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventTicketCodeController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('event_ticket_code_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $eventTicketCodes = EventTicketCode::all();

        return view('admin.eventTicketCodes.index', compact('eventTicketCodes'));
    }

    public function create()
    {
        abort_if(Gate::denies('event_ticket_code_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.eventTicketCodes.create');
    }

    public function store(StoreEventTicketCodeRequest $request)
    {
        $eventTicketCode = EventTicketCode::create($request->all());

        return redirect()->route('admin.event-ticket-codes.index');
    }

    public function edit(EventTicketCode $eventTicketCode)
    {
        abort_if(Gate::denies('event_ticket_code_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.eventTicketCodes.edit', compact('eventTicketCode'));
    }

    public function update(UpdateEventTicketCodeRequest $request, EventTicketCode $eventTicketCode)
    {
        $eventTicketCode->update($request->all());

        return redirect()->route('admin.event-ticket-codes.index');
    }

    public function show(EventTicketCode $eventTicketCode)
    {
        abort_if(Gate::denies('event_ticket_code_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.eventTicketCodes.show', compact('eventTicketCode'));
    }

    public function destroy(EventTicketCode $eventTicketCode)
    {
        abort_if(Gate::denies('event_ticket_code_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $eventTicketCode->delete();

        return back();
    }

    public function massDestroy(MassDestroyEventTicketCodeRequest $request)
    {
        EventTicketCode::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}