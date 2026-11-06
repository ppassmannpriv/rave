<?php

namespace App\Http\Requests\Site;

use App\Contracts\Cart;
use App\Models\ContentCategory;
use App\Models\PaymentMethod;
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
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email',
            'payment_method' => ['required', 'string', function ($attribute, $value, $fail) {
                if (PaymentMethod::selectableBy(auth()->user())->where('alias', $value)->exists() === false) {
                    $fail('The selected payment method is not available.');
                }
            }],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (app(Cart::class)->content()->cartItems()->exists() === false) {
                $validator->errors()->add('cart', 'There is nothing in your cart.');
            }
        });
    }
}
