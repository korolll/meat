<?php

namespace App\Http\Requests\Integrations\CashRegisters\API;

use App\Models\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculateReceiptRequest extends FormRequest
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
            'uuid' => 'nullable|uuid',
            'loyalty_card_uuid' => 'required|uuid|exists:loyalty_cards,uuid',
            'store_uuid' => [
                'required',
                'uuid',
                Rule::exists('users', 'uuid')
                    ->where('user_type_id', UserType::ID_STORE)
            ],
            'total' => 'required|numeric|min:0.01',

            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|min:1',
            'items.*.price' => 'required|numeric|min:0.01',
            'items.*.count' => 'required|numeric|min:0.01',
            'items.*.sum' => 'required|numeric|min:0.01',
            'items.*.number' => 'required|string',
        ];
    }
}
