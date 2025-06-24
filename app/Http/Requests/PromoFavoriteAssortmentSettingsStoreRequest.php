<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoFavoriteAssortmentSettingsStoreRequest extends FormRequest
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
            'threshold_amount' => 'required|numeric|between:0,999999.99',
            'number_of_sum_days' => 'required|integer|between:1,30',
            'number_of_active_days' => 'required|integer|between:1,30',
            'discount_percent' => 'required|numeric|between:0.00,100',
            'is_enabled' => 'required|boolean',
        ];
    }
}
