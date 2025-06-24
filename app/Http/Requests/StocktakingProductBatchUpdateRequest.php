<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StocktakingProductBatchUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'products' => 'required|array|min:1',
            'products.*.product_uuid' => 'required|string|uuid|exists:products,uuid',
            'products.*.quantity_new' => 'required|numeric',
            'write_off_reason_id' => 'string|exists:write_off_reasons,id',
            'comment' => 'nullable|string',
        ];
    }
}
