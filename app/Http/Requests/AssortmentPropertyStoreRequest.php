<?php

namespace App\Http\Requests;

use App\Models\AssortmentProperty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssortmentPropertyStoreRequest extends FormRequest
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
        $unique = Rule::unique('assortment_properties', 'name')
            ->whereNull('deleted_at');

        if ($this->assortment_property instanceof AssortmentProperty) {
            $unique->ignoreModel($this->assortment_property);
            $adds = [];
        } else {
            $adds = [
                'assortment_property_data_type_id' => 'required|string|exists:assortment_property_data_types,id'
            ];
        }

        return array_merge([
            'name' => [
                'required',
                'string',
                'between:2,60',
                $unique,
            ],
            'is_searchable' => [
                'boolean',
                $this->getMethod() === 'POST' ? 'required' : ''
            ],
        ], $adds);
    }
}
