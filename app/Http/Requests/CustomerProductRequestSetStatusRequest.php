<?php

namespace App\Http\Requests;

use App\Models\ProductRequestCustomerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class CustomerProductRequestSetStatusRequest extends FormRequest
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
            'product_request_customer_status_id' => 'required|exists:product_request_customer_statuses,id',
            'customer_comment' => 'required_if:product_request_customer_status_id,user-canceled|nullable|string|between:3,255',
            'supplier_rating' => 'nullable|integer|min:0|max:5'
        ];

        switch ($this->product_request_customer_status_id) {
            case ProductRequestCustomerStatus::ID_NEW:
                $rules['expected_delivery_date'] = 'required|string|after_or_equal:now'; // @todo iso rule
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

        return Arr::only($validated, ['expected_delivery_date', 'customer_comment']);
    }
}
