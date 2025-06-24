<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarStoreRequest extends FormRequest
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
            'brand_name' => 'required|string|between:2,60',
            'model_name' => 'required|string|between:2,60',
            'license_plate' => 'required|string|between:7,10|alpha_num',
            'call_sign' => 'required|string|between:1,60',
            'max_weight' => 'required|integer|digits_between:1,10',
            'is_active' => 'required|boolean',
        ];
    }
}
