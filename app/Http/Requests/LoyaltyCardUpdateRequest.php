<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoyaltyCardUpdateRequest extends FormRequest
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
            'number' => [
                'required',
                'digits_between:3,20',
                Rule::unique('loyalty_cards')
                    ->where('loyalty_card_type_uuid', $this->loyalty_card_type_uuid)
                    ->ignore($this->loyalty_card->uuid, 'uuid'),
            ],
        ];
    }
}
