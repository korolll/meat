<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoDiverseFoodSettingsStoreRequest extends FormRequest
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
            'count_purchases' => 'required|integer|between:0,99999999',
            'count_rating_scores' => 'required|integer|between:0,99999999',
            'is_enabled' => 'required|boolean',
            'discount_percent' => 'required|numeric|between:0,100',
        ];
    }
}
