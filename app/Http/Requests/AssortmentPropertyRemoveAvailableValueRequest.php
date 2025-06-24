<?php

namespace App\Http\Requests;

use App\Models\AssortmentProperty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssortmentPropertyRemoveAvailableValueRequest extends FormRequest
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
                Rule::in($assortmentProperty->available_values),
                function ($attribute, $value, $fail) use ($assortmentProperty) {
                    $exist = $assortmentProperty->assortments()->wherePivot('value', $value)->exists();
                    if ($exist) {
                        $fail($attribute . ' exist in assortments');
                    }
                }
            ]
        ];
    }
}
