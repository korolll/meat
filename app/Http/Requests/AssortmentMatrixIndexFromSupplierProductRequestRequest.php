<?php

namespace App\Http\Requests;

use App\Rules\SupplierProductRequestSuitableForNewOrderExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class AssortmentMatrixIndexFromSupplierProductRequestRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        return user() !== null;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'supplier_product_requests' => 'required|array',
            'supplier_product_requests.*.uuid' => [
                'required',
                'uuid',
                'distinct',
                new SupplierProductRequestSuitableForNewOrderExists(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getSupplierProductRequestUuids()
    {
        return Arr::pluck($this->supplier_product_requests, 'uuid');
    }
}
