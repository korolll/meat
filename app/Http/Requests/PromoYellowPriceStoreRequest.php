<?php

namespace App\Http\Requests;

use App\Models\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromoYellowPriceStoreRequest extends FormRequest
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
            'assortment_uuid' => 'required|uuid|exists:assortments,uuid',
            'price' => 'required|numeric|between:0,99999999.99',
            'is_enabled' => 'required|boolean',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'store_uuids' => 'required|array',
            'store_uuids.*' => [
                'required',
                'string',
                Rule::exists('users', 'uuid')->where(function ($query) {
                    return $query->where('user_type_id', UserType::ID_STORE);
                }),
            ]
        ];
    }
}
