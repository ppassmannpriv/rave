<?php

namespace App\Http\Requests;

use App\Models\EventTicket;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreEventTicketRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('event_ticket_create');
    }

    public function rules()
    {
        return [
            'ticket_type' => [
                'required',
            ],
            'price' => [
                'required',
            ],
            'stock' => [
                'required',
                'integer',
            ],
            'event_id' => [
                'required',
                'integer',
            ],
            'from' => [
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
                'nullable',
            ],
            'to' => [
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
                'nullable',
            ],
        ];
    }
}
