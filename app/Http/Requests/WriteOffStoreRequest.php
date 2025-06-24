<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WriteOffStoreRequest extends FormRequest
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
            'product_uuid' => [
                'required',
                'uuid',
                Rule::exists('products', 'uuid')->where('user_uuid', user()->uuid),
            ],
            'write_off_reason_id' => 'required|exists:write_off_reasons,id',
            'quantity_delta' => 'required|numeric',
            'comment' => 'nullable|string',
        ];
    }
}
