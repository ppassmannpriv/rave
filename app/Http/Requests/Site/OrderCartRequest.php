<?php

namespace App\Http\Requests\Site;

use App\Models\ContentCategory;
use App\Models\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class OrderCartRequest extends FormRequest
{
    public function authorize()
    {
        // Make a gate that checks for valid session
        // abort_if(Gate::denies('content_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'cart_item'   => 'required|array|exists:cart_items,id',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'street' => 'required|string',
            'postcode' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|in:' . implode(',', User::ALLOWED_COUNTRIES),
            'email' => 'required|email',
            'payment_method' => 'required|string|exists:payment_methods,alias',
            'general-terms-and-conditions' => 'required',
            'data-protection-and-revocation' => 'required',
        ];
    }
}
