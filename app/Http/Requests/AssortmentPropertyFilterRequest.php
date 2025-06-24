<?php

namespace App\Http\Requests;

use App\Rules\Uuid;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\In;

class AssortmentPropertyFilterRequest extends FormRequest
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
            'assortment_property' => 'array',
//            'assortment_property.uuid' => [
//                new Uuid(),
//                'required_with:assortment_property.operator,assortment_property.value',
//                'exists:assortment_properties,uuid'
//            ],
//            'assortment_property.operator' => [
//                'required_with:assortment_property.uuid,assortment_property.value',
//                new In(EloquentCollectionResponse::OPERATORS_ARRAY)
//            ],
//            'assortment_property.value' => 'required_with:assortment_property.operator,assortment_property.uuid',
        ];
    }
//
//    public function messages()
//    {
//        return [
//            'assortment_property.*.operator.in' => 'Оператор должен быть одним из: ' . implode(', ', array_map(function ($value) {
//                    return "'{$value}'";
//                }, EloquentCollectionResponse::OPERATORS_ARRAY)),
//        ];
//    }
}
