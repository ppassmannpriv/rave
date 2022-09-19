<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

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
                'unique:payments,reference,' . request()->route('payment')->id,
            ],
        ];
    }
}