<?php

namespace App\Http\Requests\Clients\API\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoyaltyCardStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'loyalty_card_type_uuid' => [
                'required',
                'uuid',
                'exists:loyalty_card_types,uuid',
                Rule::unique('loyalty_cards')
                    ->where('client_uuid', auth()->user()->uuid),
            ],
            'loyalty_card_number' => [
                'required',
                'digits_between:3,20',
                Rule::exists('loyalty_cards', 'number')
                    ->where('loyalty_card_type_uuid', $this->loyalty_card_type_uuid)
                    ->whereNull('client_uuid'),
            ],
        ];
    }
}
