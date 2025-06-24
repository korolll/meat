<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssortmentPropertyChangeDataTypeRequest extends FormRequest
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
        return [
            'assortment_property_data_type_id' => 'required|string|exists:assortment_property_data_types,id'
        ];
    }
}
