<?php

namespace App\Http\Requests\Integrations\CashRegisters\API;

use Illuminate\Foundation\Http\FormRequest;

class FindLoyaltyCardRequest extends FormRequest
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
            'user_uuid' => 'required|uuid',
            'loyalty_card_type_uuid' => 'required|uuid',
            'loyalty_card_number' => 'required|digits_between:3,20',
        ];
    }
}
