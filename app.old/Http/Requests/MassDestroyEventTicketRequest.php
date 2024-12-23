<?php

namespace App\Http\Requests;

use App\Models\EventTicket;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyEventTicketRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('event_ticket_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:event_tickets,id',
        ];
    }
}