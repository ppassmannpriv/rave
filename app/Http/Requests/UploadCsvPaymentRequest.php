<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UploadCsvPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('payment_access');
    }

    public function rules()
    {
        return [
            'csv' => [
                'required',
                'file',
                'mimes:csv',
            ],
        ];
    }
}
