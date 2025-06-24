<?php

namespace App\Http\Requests\Clients\API;

use Illuminate\Foundation\Http\FormRequest;

class FindNearbyStoresRequest extends FormRequest
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
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'limit' => 'nullable|integer|between:1,10',
            'max_distance_meters' => 'nullable|integer|between:100,100000'
        ];
    }
}
