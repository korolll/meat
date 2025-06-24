<?php

namespace App\Http\Requests;

use App\Models\LegalForm;
use App\Models\UserType;
use App\Rules\AlphaSpace;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->guest();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_type_id' => ['required', Rule::in(UserType::USER_AVAILABLE_IDS)],
            'full_name' => ['nullable', 'string', 'between:2,60', new AlphaSpace()],
            'legal_form_id' => ['required', Rule::in(LegalForm::USER_AVAILABLE_IDS)],
            'organization_name' => 'required|string|between:5,200',
            'organization_address' => 'required|string|between:5,300',
            'address' => 'required|string|between:5,300',
            'email' => 'required||between:5,50|email|unique:users',
            'phone' => ['required', new PhoneNumber()],
            'inn' => 'nullable|string|digits_between:10,12',
            'kpp' => 'nullable|string|digits:9',
            'ogrn' => 'nullable|string|digits_between:13,15',
            'region_uuid' => 'nullable|uuid|exists:regions,uuid',

            'less_zone_distance' => 'nullable|integer',
            'between_zone_distance' => 'nullable|integer',
            'more_zone_distance' => 'nullable|integer',
            'max_zone_distance' => 'nullable|integer',
            'less_zone_price' => 'nullable|numeric',
            'between_zone_price' => 'nullable|numeric',
            'more_zone_price' => 'nullable|numeric',
        ];
    }
}
