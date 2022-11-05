<?php

namespace App\Http\Requests;

use App\Models\Event;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateTimeScheduleShiftRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('time_schedule_shift_edit');
    }

    public function rules()
    {
        return [
            'description' => [
                'string',
                'required',
            ],
            'start' => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            ],
            'end' => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            ],
            'active' => [
                'boolean',
                'required',
            ],
            'event_id' => [
                'integer'
            ]
        ];
    }
}
