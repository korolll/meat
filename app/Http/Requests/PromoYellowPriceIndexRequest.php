<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoYellowPriceIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_uuid' => ['nullable', 'array'],
            'store_uuid.*' => ['uuid', 'exists:users,uuid'],
        ];
    }
}
