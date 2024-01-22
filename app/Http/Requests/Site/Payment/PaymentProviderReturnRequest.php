<?php

namespace App\Http\Requests\Site\Payment;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class PaymentProviderReturnRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'token'   => 'required|string',
            'PayerID' => 'required|string',
        ];
    }
}
