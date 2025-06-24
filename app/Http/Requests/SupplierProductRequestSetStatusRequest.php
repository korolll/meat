<?php

namespace App\Http\Requests;

use App\Models\ProductRequestSupplierStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * Class SupplierProductRequestSetStatusRequest
 *
 * @property string $supplier_comment
 * @property integer $customer_rating
 * @property array $pre_request_products
 * @property string $expected_delivery_date
 * @property string $confirmed_date
 * @property string product_request_supplier_status_id
 *
 * @package App\Http\Requests
 */
class SupplierProductRequestSetStatusRequest extends FormRequest
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
        $rules = [
            'product_request_supplier_status_id' => 'required|exists:product_request_supplier_statuses,id',
            'supplier_comment' => 'required_if:product_request_supplier_status_id,supplier-refused|nullable|string|between:3,255',
            'customer_rating' => 'nullable|integer|min:0|max:5',
            'pre_request_products' => 'array',
            'pre_request_products.*.uuid' => 'required|exists:products,uuid',
            'pre_request_products.*.quantity' => 'required|integer',
            'pre_request_products.*.delivery_date' => 'required|date|before:+1 year|after:now',
            'pre_request_products.*.confirmed_delivery_date' => 'required|date|before:+1 year|after:now',
        ];

        switch ($this->product_request_supplier_status_id) {
            case ProductRequestSupplierStatus::ID_MATCHING:
                $rules['expected_delivery_date'] = 'required|string|after_or_equal:now'; // @todo iso rule
                break;
            case ProductRequestSupplierStatus::ID_IN_WORK:
                $rules['confirmed_date'] = 'required|date|before:+1 month|after:now';
                break;
            case ProductRequestSupplierStatus::ID_ON_THE_WAY:
                $rules['confirmed_date'] = 'date|before:+1 month|after:now';
                break;
        }

        return $rules;
    }

    /**
     * @return array
     */
    public function getStatusTransitionAttributes()
    {
        $validated = $this->validated();

        return Arr::only($validated, ['expected_delivery_date', 'supplier_comment']);
    }
}
