<?php

namespace App\Http\Requests\Clients\API\Profile;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryAddressStoreRequest extends FormRequest
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
            'title' => 'required|string|between:3,256',
            'city' => 'required|string|between:2,256',
            'street' => 'required|string|between:2,256',
            'house' => 'nullable|string|between:1,256',
            'floor' => 'nullable|integer',
            'entrance' => 'nullable|integer',
            'apartment_number' => 'nullable|integer',
            'intercom_code' => 'nullable|string|between:1,16',
        ];
    }
}
