<?php

namespace App\Http\Requests\Clients\API\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ShoppingCartBulkStoreRequest extends FormRequest
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
            'items' => 'required|array',
            'items.*.uuid' => 'required|distinct|uuid|exists:assortments,uuid',
            'items.*.quantity' => 'required|numeric|between:0,99999999'
        ];
    }
}