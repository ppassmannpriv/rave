<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventTicketCodeRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('event_ticket_code_edit');
    }

    public function rules()
    {
        return [
            'code' => [
                'string',
                'min:6',
                'max:12',
                'required',
                'unique:event_ticket_codes,code,'.request()->route('event_ticket_code')->id,
            ],
        ];
    }
}
