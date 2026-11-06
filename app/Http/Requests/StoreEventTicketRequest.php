<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

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
            'cap' => [
                'required',
                'integer',
            ],
            'event_id' => [
                'required',
                'integer',
            ],
            'from' => [
                'date_format:'.config('panel.date_format').' '.config('panel.time_format'),
                'nullable',
            ],
            'to' => [
                'date_format:'.config('panel.date_format').' '.config('panel.time_format'),
                'nullable',
            ],
        ];
    }
}
