<?php

namespace App\Rules;

use App\Models\ProductRequests\SupplierProductRequest;
use App\Models\ProductRequestSupplierStatus;
use Illuminate\Contracts\Validation\Rule;

class SupplierProductRequestSuitableForNewOrderExists implements Rule
{
    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return SupplierProductRequest::whereKey($value)
            ->where('supplier_user_uuid', user()->uuid)
            ->doesntHave('relatedCustomerProductRequests')
            ->exists();
    }

    /**
     * @return string
     */
    public function message()
    {
        return trans('validation.exists');
    }
}
