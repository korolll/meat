<?php

namespace App\Http\Requests\Clients\API;

use Illuminate\Foundation\Http\FormRequest;

class AssortmentSearchRequest extends FormRequest
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
            'phrase' => 'required|string|between:3,255',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:1000',
        ];
    }
}
