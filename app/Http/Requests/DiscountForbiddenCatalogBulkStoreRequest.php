<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscountForbiddenCatalogBulkStoreRequest extends FormRequest
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
            'catalog_uuids' => 'required|array|min:1',
            'catalog_uuids.*' => [
                'required',
                'uuid',
                'distinct',
                'exists:catalogs,uuid',
                'unique:discount_forbidden_catalogs,catalog_uuid',
            ],
        ];
    }
}
