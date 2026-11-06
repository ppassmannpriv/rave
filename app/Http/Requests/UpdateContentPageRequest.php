<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContentPageRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('content_page_edit');
    }

    public function rules()
    {
        return [
            'title' => [
                'string',
                'required',
            ],
            'path' => [
                'string',
                'required',
                // 'unique:content_pages,path'
            ],
            'categories.*' => [
                'integer',
            ],
            'categories' => [
                'array',
            ],
            'tags.*' => [
                'integer',
            ],
            'tags' => [
                'array',
            ],
            'enabled' => [
                'boolean',
                'required',
            ],
            'index' => [
                'boolean',
            ],
        ];
    }
}
