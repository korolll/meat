<?php

namespace App\Http\Requests\Clients\API;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResetViaLoyaltyCardRequest extends FormRequest
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
            'loyalty_card_type_uuid' => 'required|uuid|exists:loyalty_card_types,uuid',
            'loyalty_card_number' => [
                'required',
                'digits_between:3,20',
                Rule::exists('loyalty_cards', 'number')
                    ->where('loyalty_card_type_uuid', $this->loyalty_card_type_uuid)
                    ->whereNotNull('client_uuid'),
            ],
            'old_phone_last_four_digits' => 'required|digits:4',
            'new_phone' => ['required', new PhoneNumber(), 'unique:clients,phone,NULL,uuid,deleted_at,NULL'],
            'code' => 'digits:4',
        ];
    }
}
