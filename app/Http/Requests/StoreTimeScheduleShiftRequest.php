<?php

namespace App\Http\Requests;

use App\Models\Event;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreTimeScheduleShiftRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('time_schedule_shift_create');
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
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            ],
            'end' => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            ],
            'repeat' => [
                'integer',
                'min:0'
            ],
            'crew_only' => [
                'boolean',
                'required',
            ],
        ];
    }
}
