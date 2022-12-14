<?php

namespace App\Http\Requests\Site;

use App\Models\ContentCategory;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class RemoveFromCartRequest extends FormRequest
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
        ];
    }
}
