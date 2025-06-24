<?php

namespace App\Http\Requests\Clients\API\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class ShoppingListStoreRequest extends FormRequest
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
            'name' => 'required|string|between:2,160',
            'assortments' => 'nullable|array',
            'assortments.*.uuid' => 'required|distinct|uuid|exists:assortments,uuid',
            'assortments.*.quantity' => 'required|integer|between:1,99999999'
        ];
    }

    /**
     * @return array
     */
    public function assortments(): array
    {
        return collect(Arr::get($this->validated(), 'assortments', []))->mapWithKeys(function($item) {
            return [$item['uuid'] => ['quantity' => Arr::get($item, 'quantity')]];
        })->all();
    }
}
