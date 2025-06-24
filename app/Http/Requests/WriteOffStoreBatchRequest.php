<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WriteOffStoreBatchRequest extends FormRequest
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
            'products' => 'required|array|min:1',
            'products.*.product_uuid' => [
                'required',
                'uuid',
                'distinct',
                Rule::exists('products', 'uuid')->where('user_uuid', user()->uuid),
            ],
            'products.*.quantity_delta' => 'required|numeric',

            'write_off_reason_id' => 'required|exists:write_off_reasons,id',
            'comment' => 'nullable|string',
        ];
    }
}
