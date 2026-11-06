<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('payment_create');
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
                'unique:payments',
            ],
        ];
    }
}
