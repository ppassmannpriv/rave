<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventTicketCodeRequest;
use App\Http\Requests\UpdateEventTicketCodeRequest;
use App\Http\Resources\Admin\EventTicketCodeResource;
use App\Models\EventTicketCode;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventTicketCodeApiController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('event_ticket_code_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new EventTicketCodeResource(EventTicketCode::all());
    }

    public function store(StoreEventTicketCodeRequest $request)
    {
        $eventTicketCode = EventTicketCode::create($request->all());

        return (new EventTicketCodeResource($eventTicketCode))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(EventTicketCode $eventTicketCode)
    {
        abort_if(Gate::denies('event_ticket_code_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new EventTicketCodeResource($eventTicketCode);
    }

    public function update(UpdateEventTicketCodeRequest $request, EventTicketCode $eventTicketCode)
    {
        $eventTicketCode->update($request->all());

        return (new EventTicketCodeResource($eventTicketCode))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(EventTicketCode $eventTicketCode)
    {
        abort_if(Gate::denies('event_ticket_code_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $eventTicketCode->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}