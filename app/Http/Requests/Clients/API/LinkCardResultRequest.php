<?php

namespace App\Http\Requests\Clients\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LinkCardResultRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** @var \App\Models\Client $client */
        $client = auth()->user();
        return [
            'orderId' => [
                'required',
                'uuid',
                Rule::exists('client_credit_cards', 'generated_order_uuid')
                    ->where('client_uuid', $client->uuid)
                    ->whereNull('binding_id')
            ],
        ];
    }
}
