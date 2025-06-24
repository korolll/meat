<?php

namespace App\Http\Requests;

use App\Models\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromoYellowPriceBulkStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'assortment_uuids' => 'required|array|min:1',
            'assortment_uuids.*' => 'required|uuid|exists:assortments,uuid',
            'price' => 'required|numeric|between:0,99999999.99',
            'is_enabled' => 'required|boolean',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'store_uuids' => 'required|array|min:1',
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
