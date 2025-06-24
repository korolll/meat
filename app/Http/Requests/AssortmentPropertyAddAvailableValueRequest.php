<?php

namespace App\Http\Requests;

use App\Models\AssortmentProperty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssortmentPropertyAddAvailableValueRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** @var AssortmentProperty $assortmentProperty */
        $assortmentProperty = $this->assortmentProperty;

        return [
            'value' => [
                'required',
                'string',
                'between:2,255',
                Rule::notIn($assortmentProperty->available_values),
            ]
        ];
    }
}
