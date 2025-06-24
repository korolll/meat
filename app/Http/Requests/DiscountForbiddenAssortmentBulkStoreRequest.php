<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscountForbiddenAssortmentBulkStoreRequest extends FormRequest
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
            'assortment_uuids' => 'required|array|min:1',
            'assortment_uuids.*' => [
                'required',
                'uuid',
                'distinct',
                'exists:assortments,uuid',
                'unique:discount_forbidden_assortments,assortment_uuid',
            ],
        ];
    }
}
