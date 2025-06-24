<?php

namespace App\Services\Framework\Http;

use Illuminate\Foundation\Http\FormRequest;

class CollectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'where' => 'array',
            'where.*' => 'array|between:2,3',
            'order_by' => 'array|min:1',
            'order_by.*' => 'string|in:asc,desc,ASC,DESC',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:1000',
        ];
    }
}
