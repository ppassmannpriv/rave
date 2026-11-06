<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeScheduleShiftRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('time_schedule_shift_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'description' => [
                'string',
            ],
            'start' => [
                'required',
                'date_format:'.config('panel.date_format').' '.config('panel.time_format'),
            ],
            'end' => [
                'required',
                'date_format:'.config('panel.date_format').' '.config('panel.time_format'),
            ],
            'crew_only' => [
                'boolean',
                'required',
            ],
        ];
    }
}
