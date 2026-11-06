<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('payment_edit');
    }

    public function rules()
    {
        return [
            'provider' => [
                'required',
            ],
            'amount' => [
                'required',
            ],
            'state' => [
                'required',
            ],
            'reference' => [
                'string',
                'min:6',
                'required',
                'unique:transactions,reference,'.request()->route('payment'),
            ],
        ];
    }
}
