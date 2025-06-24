<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ProductSetDeliveryWeekdaysRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'products.*.uuid' => [
                'required',
                'distinct',
                'uuid',
                Rule::exists('products', 'uuid')->where('user_uuid', user()->uuid),
            ],
            'delivery_weekdays' => 'present|array',
            'delivery_weekdays.*' => 'integer|distinct|between:0,6',
        ];
    }

    /**
     * @return array
     */
    public function getProductUuids()
    {
        return Arr::pluck($this->products, 'uuid');
    }
}
