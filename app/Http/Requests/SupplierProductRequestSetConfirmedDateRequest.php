<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class SupplierProductRequestSetConfirmedDateRequest
 *
 * @property array $pre_request_products
 * @property string $confirmed_date
 *
 * @package App\Http\Requests
 */
class SupplierProductRequestSetConfirmedDateRequest extends FormRequest
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
            'confirmed_date' => 'required|date|before:+1 month|after:now',
            'pre_request_products' => 'array',
            'pre_request_products.*.uuid' => 'required|exists:products,uuid',
            'pre_request_products.*.quantity' => 'required|integer',
            'pre_request_products.*.delivery_date' => 'required|date|before:+1 year|after:now',
            'pre_request_products.*.confirmed_delivery_date' => 'required|date|before:+1 year|after:now',
        ];
    }
}
