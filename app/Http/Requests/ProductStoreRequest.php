<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Rules\FileExists;
use App\Rules\PrivateCatalogExists;
use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
        if ($this->exists('*RequestFilters')) {
            return [
                '*RequestFilters' => 'array',
            ];
        }

        return [
            'catalog_uuid' => ['required', 'uuid', new PrivateCatalogExists()],
            'assortment_uuid' => 'required|uuid|exists:assortments,uuid',
            'quantum' => 'required|integer|between:0,99999999',
            'min_delivery_time' => 'required|integer|between:1,99999999',
            'min_quantum_in_order' => 'required|integer|digits_between:1,10',
            'price_recommended' => 'nullable|numeric|between:0,9999999999999999999.99',
            'volume' => 'required|numeric|between:0,99999999999.99',
            'delivery_weekdays' => 'present|array',
            'delivery_weekdays.*' => 'integer|distinct|between:0,6',
            'files' => 'array|between:0,50',
            'files.*.uuid' => [
                'required',
                'distinct',
                'uuid',
                new FileExists(FileCategory::ID_PRODUCT_FILE),
            ],
            'files.*.public_name' => 'nullable|string|between:0,255'
        ];
    }
}
