<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssortmentMatrixStoreRequest extends FormRequest
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
            'assortment_uuid' => 'required|uuid|exists:assortments,uuid',
        ];
    }
}
